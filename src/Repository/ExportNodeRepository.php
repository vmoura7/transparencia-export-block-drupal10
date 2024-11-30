<?php

namespace Drupal\transparencia_export_block\Repository;

use Drupal\node\Entity\Node;

/**
 * Classe repositório para buscar dados do nó.
 */
class ExportNodeRepository
{

  /**
   * Obtém os dados do nó.
   *
   * @param int $nid
   *   ID do nó.
   *
   * @return array|null
   *   Dados formatados do nó ou NULL se o nó não for encontrado.
   */
  public function getNodeData($nid)
  {
    $node = Node::load($nid);

    if (!$node) {
      return NULL;
    }

    // Retorna os dados básicos do nó.
    $data = [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
      'type' => $node->bundle(),
      'status' => $node->isPublished(),
      'created' => $node->getCreatedTime(),
      'fields' => [],
    ];

    // Processa os campos do nó.
    foreach ($node->getFields() as $field_name => $field) {
      if (!$field->isEmpty()) {
        $data['fields'][$field_name] = $field->getValue();
      }
    }

    return $data;
  }
}
