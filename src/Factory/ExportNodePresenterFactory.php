<?php

namespace Drupal\transparencia_export_block\Factory;

use Drupal\transparencia_export_block\Presenter\JsonExportNodePresenter;
use Drupal\transparencia_export_block\Presenter\XmlExportNodePresenter;
use Drupal\transparencia_export_block\Presenter\PdfExportNodePresenter;
use Drupal\transparencia_export_block\Presenter\ExportNodePresenterInterface;

class ExportNodePresenterFactory
{
  /**
   * Cria um presenter baseado no formato.
   *
   * @param string $format
   *   Formato desejado (e.g., 'json', 'xml').
   *
   * @return ExportNodePresenterInterface
   *   Instância do presenter.
   *
   * @throws \Exception
   *   Exceção se o formato não for suportado.
   */
  public static function create($format)
  {
    $presenters = [
      'json' => JsonExportNodePresenter::class,
      'xml' => XmlExportNodePresenter::class,
      'pdf' => PdfExportNodePresenter::class,
    ];

    if (!isset($presenters[$format])) {
      throw new \Exception("Formato '$format' não suportado.");
    }

    return new $presenters[$format]();
  }
}
