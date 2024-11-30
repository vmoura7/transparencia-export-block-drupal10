<?php

namespace Drupal\transparencia_export_block\Presenter;

use Mpdf\Mpdf;

class PdfExportNodePresenter extends BaseExportNodePresenter
{
  /**
   * Retorna os cabeçalhos HTTP para PDF.
   *
   * @return array Cabeçalhos HTTP.
   */
  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }

  /**
   * Converte os dados formatados para uma string PDF.
   *
   * @param array $data
   *   Dados formatados.
   *
   * @return string
   *   Dados no formato PDF.
   */
  protected function convertFormattedDataToString(array $data): string
  {
    // Log dos dados recebidos.
    \Drupal::logger('transparencia_export_block')->info('Dados recebidos no presenter PDF: <pre>@data</pre>', [
      '@data' => print_r($data, TRUE),
    ]);

    try {
      // Inicializa o mPDF
      $mpdf = new Mpdf();

      // Cria o HTML a partir dos dados.
      $html = $this->buildHtmlFromData($data);

      // Log do HTML gerado
      \Drupal::logger('transparencia_export_block')->info('HTML gerado para o PDF: <pre>@html</pre>', [
        '@html' => $html,
      ]);

      // Configura o mPDF.
      $mpdf->WriteHTML($html);

      // Retorna o PDF como string binária.
      return $mpdf->Output('', 'S'); // 'S' para retornar o PDF como string.
    } catch (\Exception $e) {
      // Log de erro
      \Drupal::logger('transparencia_export_block')->error('Erro ao gerar o PDF: @message', [
        '@message' => $e->getMessage(),
      ]);

      // Re-throw para que o erro não seja silencioso.
      throw $e;
    }
  }

  /**
   * Gera HTML a partir dos dados.
   *
   * @param array $data
   *   Dados formatados.
   *
   * @return string
   *   HTML estruturado.
   */
  private function buildHtmlFromData(array $data): string
  {
    // Estilo para o PDF (CSS)
    $css = "
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #ffffff;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.5;
    position: relative;
  }
  .header {
    text-align: left;
    font-size: 14px;
    font-weight: bold;
    color: #444;
    padding: 20px 30px;
    border-bottom: 2px solid #333;
  }
  .content {
    margin: 50px 30px;
    font-size: 12px;
    padding: 20px;
    position: relative;
    background-color: rgba(255, 255, 255, 0.85);
  }
  .title {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
    padding-bottom: 10px;
  }
  .field {
    margin-bottom: 10px;
  }
  .field strong {
    color: #444;
  }
  .field a {
    color: #000;
    text-decoration: none;
  }
  .footer {
    font-size: 10px;
    color: #777;
    position: fixed;
    bottom: 0;
    width: 100%;
    text-align: center;
    padding: 10px 0;
    border-top: 1px solid #333;
    background: linear-gradient(to top, #f9f9f9, #ffffff);
  }
  .border-decorator::before,
  .border-decorator::after {
    content: '';
    position: absolute;
    border: 1px solid #aaa;
  }
  .border-decorator::before {
    top: 10px;
    left: 10px;
    width: calc(100% - 20px);
    height: calc(100% - 20px);
  }
  .border-decorator::after {
    top: 30px;
    left: 30px;
    width: calc(100% - 60px);
    height: calc(100% - 60px);
    border-width: 1px;
  }
  @page { margin: 20mm; }
    .content { page-break-inside: avoid; }
  ";



    // Acessando os valores de campos no array de dados
    $titulo = isset($data['campos']['titulo'][0]['valor']) ? $data['campos']['titulo'][0]['valor'] : 'Sem título';
    $texto = isset($data['campos']['texto'][0]['valor']) ? $data['campos']['texto'][0]['valor'] : 'Texto não disponível';
    $nid = isset($data['id']) ? $data['id'] : 'não disponível';
    $criadoEm = isset($data['criado_em']) ? $data['criado_em'] : 'não disponível';
    $tipoDeConteudo = isset($data['tipo_de_conteudo']) ? $data['tipo_de_conteudo'] : 'não disponível';
    $exportadoEm = isset($data['exportado_em']) ? $data['exportado_em'] : 'não disponível';
    $url = isset($data['url']) ? $data['url'] : 'URL não disponível';

    // Recupera o nome do site no Drupal
    $siteName = \Drupal::config('system.site')->get('name');

    // Gerando o HTML
    $html = "
      <html>
        <head>
          <style>{$css}</style>
        </head>
        <body>
          <div class='header'>{$siteName}</div>
          <div class='content'>
            <div class='title'>{$titulo}</div>
            <div class='field'><strong>Id:</strong> {$nid}</div>
            <div class='field'><strong>Criado em:</strong> {$criadoEm}</div>
            <div class='field'><strong>Tipo de Conteúdo:</strong> {$tipoDeConteudo}</div>
            <div class='field'>{$texto}</div>
            <div class='field'><strong>Exportado em:</strong> {$exportadoEm}</div>
            <div class='field'><strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a></div>
          </div>
          <div class='footer'>Gerado pelo Portal da Transparência - " . date('d/m/Y H:i:s') . "</div>
        </body>
      </html>
    ";

    return $html;
  }
}
