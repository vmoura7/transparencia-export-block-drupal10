<?php

namespace Drupal\transparencia_export_block\Translator;

class FieldDictionary
{
  /**
   * Dicionário de campos com traduções para exibição.
   *
   * @return array
   */
  public static function getFieldTranslations(): array
  {
    return [
      'nid' => 'id',
      'title' => 'titulo',
      'type' => 'tipo_de_conteudo',
      'created' => 'criado_em',
      'changed' => 'alterado_em',
      'exported_at' => 'exportado_em',
      'path' => 'caminho',
      'gva_pagebuilder_content' => 'conteudo_da_pagina',
      'body' => 'texto',
      'target_id' => 'categoria',
      'value' => 'valor',
      'body' => 'texto',
      'content' => 'conteudo',
    ];
  }

  /**
   * Dicionário de traduções para os campos dentro de 'fields'.
   *
   * @return array
   */
  public static function getFieldTranslationsForFields(): array
  {
    return [
      'type' => 'tipo_de_conteudo',
      'title' => 'titulo',
      'created' => 'criado_em',
      'changed' => 'alterado_em',
      'gva_pagebuilder_content' => 'conteudo_da_pagina',
      'target_id' => 'categoria',
      'value' => 'valor',
      'body' => 'texto',
      'content' => 'conteudo',
    ];
  }
}
