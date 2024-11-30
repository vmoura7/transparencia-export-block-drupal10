<?php

namespace Drupal\transparencia_export_block\Presenter;

class XmlExportNodePresenter extends BaseExportNodePresenter
{
  /**
   * Retorna os cabeçalhos HTTP para XML.
   *
   * @return array Cabeçalhos HTTP.
   */
  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/xml; charset=UTF-8'];
  }

  /**
   * Converte os dados formatados para uma string XML.
   *
   * @param array $data
   *   Dados formatados.
   *
   * @return string
   *   Dados no formato XML.
   */
  protected function convertFormattedDataToString(array $data): string
  {
    // Inicializa a tag raiz como <pagina>.
    $xml = new \SimpleXMLElement('<pagina/>');

    // Converte os dados para XML.
    $this->arrayToXml($data, $xml);

    // Retorna o XML formatado.
    return $xml->asXML();
  }

  /**
   * Adiciona os dados do array ao XML.
   *
   * @param array $data
   * @param \SimpleXMLElement $xml
   */
  private function arrayToXml(array $data, \SimpleXMLElement $xml): void
  {
    foreach ($data as $key => $value) {
      // Substitui índices numéricos por "item" para XML válido.
      $key = is_numeric($key) ? 'item_' . $key : $key;

      if (is_array($value)) {
        // Cria um novo nó se o valor for um array.
        $subnode = $xml->addChild($key);
        $this->arrayToXml($value, $subnode);
      } else {
        // Adiciona o valor ao nó.
        $xml->addChild($key, htmlspecialchars((string) $value));
      }
    }
  }
}
