<?php

namespace Drupal\transparencia_export_block\Presenter;

use Drupal\transparencia_export_block\Translator\FieldTranslator;

abstract class BaseExportNodePresenter implements ExportNodePresenterInterface
{

  /**
   * Tradutor para campos.
   *
   * @var \Drupal\transparencia_export_block\Translator\FieldTranslator
   */
  protected FieldTranslator $fieldTranslator;

  public function __construct()
  {
    $this->fieldTranslator = new FieldTranslator();
  }

  /**
   * Formata os dados básicos do nó.
   */
  public function format(array $data): string
  {
    // Campos a serem omitidos.
    $fieldsToOmit = $this->getFieldsToOmit();

    // Remove os campos omitidos.
    $fields = array_filter(
      $data['fields'] ?? [],
      fn($key) => !in_array($key, $fieldsToOmit, true),
      ARRAY_FILTER_USE_KEY
    );

    // Convertendo os campos 'created' e 'changed' para o formato de data.
    $created = $this->convertToDate($data['created'] ?? null);
    $changed = $this->convertToDate($data['changed'] ?? null);

    // Estrutura básica do nó exportado.
    $formatted = [
      'nid' => $data['nid'] ?? '',
      'title' => $data['title'] ?? '',
      'type' => $data['type'] ?? '',
      'created' => $created,
      'fields' => [
        'title' => $fields['title'] ?? [],
        'changed' => $changed,
      ],
      'exported_at' => date('d-m-Y H:i:s'),
      'url' => $data['url'] ?? '',
    ];

    // Processa o conteúdo do pagebuilder e do corpo.
    $this->processContent($formatted, $fields);

    // Remove campos vazios.
    $formatted['fields'] = array_filter($formatted['fields'], function ($value) {
      if (is_array($value)) {
        return !empty($value);
      }
      return isset($value) && $value !== '';
    });

    // Traduz os campos antes de retornar.
    $formatted = $this->fieldTranslator->translate($formatted);

    // Retorna os dados formatados como string (json, xml, etc.).
    return $this->convertFormattedDataToString($formatted);
  }

  /**
   * Processa o conteúdo do pagebuilder e do corpo.
   * Esta função centraliza a lógica de processamento de conteúdo,
   * evitando repetição em classes derivadas.
   */
  protected function processContent(array &$formatted, array $fields): void
  {
    $this->processPagebuilderContent($formatted, $fields);
    $this->processBodyContent($formatted, $fields);
    // Adicione aqui o processamento de outros campos de conteúdo, se necessário.
  }


  /**
   * Processa o conteúdo do pagebuilder.
   */
  protected function processPagebuilderContent(array &$formatted, array $fields): void
  {
    if (!isset($fields['gva_pagebuilder_content'])) {
      return;
    }
    $pagebuilderContent = $this->extractPagebuilderItems($fields['gva_pagebuilder_content']);
    if (!empty($pagebuilderContent)) {
      $formatted['fields']['gva_pagebuilder_content'] = $pagebuilderContent;
    }
  }

  /**
   * Processa o conteúdo do corpo.
   */
  protected function processBodyContent(array &$formatted, array $fields): void
  {
    if (!isset($fields['body']) || empty($fields['body'])) {
      return;
    }
    $bodyContent = $this->extractBodyItems($fields['body']);
    if (!empty($bodyContent)) {
      $formatted['fields']['body'] = $bodyContent;
    }
  }

  /**
   * Extrai itens do conteúdo do pagebuilder.
   */
  protected function extractPagebuilderItems(array $pagebuilderFields): array
  {
    $extractedItems = [];
    foreach ($pagebuilderFields as $item) {
      $decoded = json_decode($item['value'] ?? '', true);
      if ($decoded) {
        $extractedItems = array_merge(
          $extractedItems,
          $this->extractContent($decoded)
        );
      }
    }
    return $extractedItems;
  }

  /**
   * Extrai itens do conteúdo do corpo.
   */
  protected function extractBodyItems(array $bodyFields): array
  {
    $bodyContent = [];
    foreach ($bodyFields as $bodyItem) {
      $cleanedValue = $this->cleanContent($bodyItem['value'] ?? '');
      if (!empty($cleanedValue)) {
        $bodyContent[] = [
          'value' => $cleanedValue,
        ];
      }
    }
    return $bodyContent;
  }

  /**
   * Extrai conteúdo do pagebuilder.  (Métodos auxiliares mantidos intactos)
   */
  protected function extractContent(array $pagebuilderContent): array
  {
    $items = [];
    $lastTitle = '';
    foreach ($pagebuilderContent as $row) {
      if (!isset($row['columns'])) {
        continue;
      }
      $rowItems = $this->extractRowItems($row['columns'], $lastTitle);
      $items = array_merge($items, $rowItems);
    }
    return $this->filterValidItems($items);
  }

  protected function extractRowItems(array $columns, string &$lastTitle): array
  {
    $rowItems = [];
    foreach ($columns as $column) {
      if (!isset($column['elements'])) {
        continue;
      }
      $columnItems = $this->extractColumnItems($column['elements'], $lastTitle);
      $rowItems = array_merge($rowItems, $columnItems);
    }
    return $rowItems;
  }

  protected function extractColumnItems(array $elements, string &$lastTitle): array
  {
    $columnItems = [];
    foreach ($elements as $element) {
      $title = $this->cleanContent($element['settings']['title'] ?? '');
      $content = $this->cleanContent($element['settings']['content'] ?? '');
      if (!empty($title)) {
        $lastTitle = $title;
      }
      if (!empty($content)) {
        $columnItems[] = [
          'title' => $lastTitle,
          'content' => $content,
        ];
        $lastTitle = '';
      }
    }
    return $columnItems;
  }

  protected function filterValidItems(array $items): array
  {
    return array_values(array_filter(
      $items,
      fn($item) => !empty($item['title']) || !empty($item['content'])
    ));
  }

  /**
   * Converte os dados formatados para uma string (dependendo do formato de exportação).
   *
   * @param array $data Dados formatados.
   *
   * @return string Dados no formato correto (json, xml, etc.).
   */
  abstract protected function convertFormattedDataToString(array $data): string;

  /**
   * Converte um timestamp Unix para o formato de data desejado.
   *
   * @param mixed $timestamp O timestamp a ser convertido (em segundos desde a Era Unix).
   *
   * @return string A data formatada como 'd-m-Y H:i:s'.
   */
  private function convertToDate($timestamp): ?string
  {
    if ($timestamp) {
      // Verifica se já é um timestamp Unix válido.
      if (is_numeric($timestamp)) {
        return date('d-m-Y H:i:s', (int) $timestamp);
      }
      // Caso contrário, tenta converter a string usando strtotime.
      return date('d-m-Y H:i:s', strtotime($timestamp));
    }
    return null;
  }

  /**
   * Retorna a lista de campos a serem omitidos.
   */
  protected function getFieldsToOmit(): array
  {
    return [
      'uuid',
      'vid',
      'langcode',
      'revision_timestamp',
      'revision_uid',
      'revision_log',
      'uid',
      'promote',
      'sticky',
      'default_langcode',
      'revision_default',
      'revision_translation_affected',
      'gva_node_layout',
      'gva_breadcrumb',
      'gva_header',
      'gva_node_class',
      'gva_pagebuilder_enable',
      'menu_link',
    ];
  }

  /**
   * Limpa o conteúdo (remove tags HTML e espaços extras).
   */
  protected function cleanContent(string $content): string
  {
    $content = strip_tags($content); // Remove tags HTML.
    $content = preg_replace('/\s+/', ' ', $content); // Remove quebras de linha e espaços extras.
    return trim(str_replace('&nbsp;', '', $content)); // Remove &nbsp; e espaços ao redor.
  }
}
