<?php

namespace Drupal\transparencia_export_block\Presenter;

class JsonExportNodePresenter extends BaseExportNodePresenter
{

  /**
   * Retorna os cabeçalhos HTTP para JSON.
   *
   * @return array Cabeçalhos HTTP.
   */
  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/json'];
  }

  /**
   * Converte os dados formatados para uma string JSON.
   *
   * @param array $data Dados formatados.
   *
   * @return string Dados no formato JSON.
   */
  protected function convertFormattedDataToString(array $data): string
  {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  }

  /**
   * Processa campos adicionais específicos para JSON.  (Agora vazio, pois a lógica está em BaseExportNodePresenter)
   */
  protected function processAdditionalFields(array &$formatted, array $fields): void
  {
    // Nenhuma ação adicional necessária aqui.
  }
}
