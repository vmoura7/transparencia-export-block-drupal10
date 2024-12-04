<?php

namespace Drupal\transparencia_export_block\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\transparencia_export_block\Repository\ExportNodeRepository;
use Drupal\transparencia_export_block\Factory\ExportNodePresenterFactory;
use Drupal\transparencia_export_block\Decorator\ExportNodeDecorator;
use Drupal\views\Views;
use Drupal\node\Entity\Node;

class ExportController
{

  public function export($identifier, $format = 'json')
  {
    // Verifica se o identificador é um número (possível Node ID).
    if (is_numeric($identifier)) {
      return $this->exportNode($identifier, $format);
    }

    // Caso contrário, trata como uma View.
    return $this->exportView($identifier, $format);
  }

  private function exportNode($nid, $format)
  {
    $repository = new ExportNodeRepository();

    // Busca os dados do nó.
    $data = $repository->getNodeData($nid);

    if (!$data) {
      return new Response(json_encode(['error' => 'Nó não encontrado.']), 404, ['Content-Type' => 'application/json']);
    }

    try {
      // Adiciona a URL aos dados do nó usando o decorador.
      $decorator = \Drupal::service('transparencia_export_block.export_node_decorator');
      $dataWithUrl = $decorator->addUrl($data);

      // Cria o presenter com base no formato.
      $presenter = ExportNodePresenterFactory::create($format);

      // Obtém a saída formatada e os headers apropriados.
      $formattedData = $presenter->format($dataWithUrl);
      $headers = $presenter->getHeaders();

      return new Response($formattedData, 200, $headers);
    } catch (\Exception $e) {
      return new Response(json_encode(['error' => $e->getMessage()]), 400, ['Content-Type' => 'application/json']);
    }
  }

  private function exportView($identifier, $format)
  {
    // Converte o identificador de volta para o nome da view.
    $view_name = str_replace('-', '/', $identifier);
    $view = Views::getView($view_name);

    if (!$view) {
      return new Response(json_encode(['error' => 'View não encontrada.']), 404, ['Content-Type' => 'application/json']);
    }

    try {
      // Configura e executa a view.
      $view->setDisplay('default');
      $view->execute();

      // Obtém os campos da view.
      $fields = [];
      foreach ($view->field as $field_name => $field) {
        $fields[$field_name] = $field->getLabel();
      }

      $data = [
        'view_id' => $view_name,
        'fields' => $fields,
        'results' => $view->result,
      ];

      // Cria o presenter com base no formato.
      $presenter = ExportNodePresenterFactory::create($format);

      // Obtém a saída formatada e os headers apropriados.
      $formattedData = $presenter->format($data);
      $headers = $presenter->getHeaders();

      return new Response($formattedData, 200, $headers);
    } catch (\Exception $e) {
      return new Response(json_encode(['error' => $e->getMessage()]), 400, ['Content-Type' => 'application/json']);
    }
  }
}
