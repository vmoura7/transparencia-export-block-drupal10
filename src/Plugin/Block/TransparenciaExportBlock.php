<?php

namespace Drupal\transparencia_export_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\transparencia_export_block\Repository\ExcludedPathsRepository;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Transparência Export Block' block.
 *
 * @Block(
 *   id = "transparencia_export_block",
 *   admin_label = @Translation("Transparência Export Block")
 * )
 */
class TransparenciaExportBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  protected $excludedPathsRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExcludedPathsRepository $excludedPathsRepository)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->excludedPathsRepository = $excludedPathsRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('transparencia_export_block.excluded_paths_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $module_path = \Drupal::service('extension.list.module')->getPath('transparencia_export_block');
    $file_url_generator = \Drupal::service('file_url_generator');
    $json_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/json-icon.svg');
    $xml_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/xml-icon.svg');
    $pdf_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/pdf-icon.svg');
    $print_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/print-icon.svg');

    // Obter a URL atual
    $current_path = \Drupal::service('path.current')->getPath();
    $current_route_name = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);

    // Aqui, você pode acessar os caminhos excluídos através do repositório.
    $excluded_paths = $this->excludedPathsRepository->getExcludedPaths();

    // Criar um array para os botões a serem renderizados
    $rendered_buttons = [];

    // Verificar se a URL atual está nos caminhos excluídos
    if (!in_array($current_route_name, $excluded_paths)) {
      $rendered_buttons[] = '
                <div id="export-json-button" class="export-button">
                    <img src="' . $json_icon_url . '" alt="Ícone de Exportar JSON" height="24" width="24" />
                </div>
                <div id="export-xml-button" class="export-button">
                    <img src="' . $xml_icon_url . '" alt="Ícone de Exportar XML" height="24" width="24" />
                </div>
                <div id="export-pdf-button" class="export-button">
                    <img src="' . $pdf_icon_url . '" alt="Ícone de Exportar PDF" height="24" width="24" />
                </div>
                <div id="print-button" class="export-button">
                    <img src="' . $print_icon_url . '" alt="Ícone de Imprimir" height="24" width="24" />
                </div>
            ';
    }

    return [
      '#markup' => '<div class="export-json-container">' . implode('', $rendered_buttons) . '</div>',
      '#attached' => [
        'library' => [
          'transparencia_export_block/export_block',
        ],
      ],
    ];
  }
}
