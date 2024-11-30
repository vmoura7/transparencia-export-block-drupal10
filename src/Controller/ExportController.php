<?php

namespace Drupal\transparencia_export_block\Controller;

use Symfony\Component\HttpFoundation\Response;
use Drupal\transparencia_export_block\Repository\ExportNodeRepository;
use Drupal\transparencia_export_block\Factory\ExportNodePresenterFactory;
use Drupal\transparencia_export_block\Decorator\ExportNodeDecorator;

class ExportController
{
  public function export($nid, $format = 'json')
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
}
