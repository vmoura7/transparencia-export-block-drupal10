<?php

namespace Drupal\transparencia_export_block\Presenter;

interface ExportNodePresenterInterface
{
  /**
   * Formata os dados para exportação.
   *
   * @param array $data
   *   Dados do nó.
   *
   * @return string
   *   Dados formatados.
   */
  public function format(array $data): string;

  /**
   * Retorna os headers apropriados para o formato.
   *
   * @return array
   *   Headers HTTP.
   */
  public function getHeaders(): array;
}
