<?php

namespace Drupal\transparencia_export_block\Translator;

class FieldTranslator
{
  /**
   * Traduz os campos usando o dicionário fornecido.
   *
   * @param array $data
   * @return array
   */
  public function translate(array $data): array
  {
    $fieldDictionary = FieldDictionary::getFieldTranslations();
    $fieldTranslationsForFields = FieldDictionary::getFieldTranslationsForFields();

    // Traduz os campos principais (não dentro de 'fields').
    $translatedData = $this->translateFields($data, $fieldDictionary);

    // Traduz o nome da chave 'fields' para 'campos'.
    if (isset($translatedData['fields'])) {
      $translatedData['campos'] = $this->translateFields($translatedData['fields'], $fieldTranslationsForFields);
      unset($translatedData['fields']);
    }

    // Reordena os campos principais.
    return $this->reorderFields($translatedData);
  }

  /**
   * Traduz os campos principais do array de dados.
   *
   * @param array $data
   * @param array $fieldDictionary
   * @return array
   */
  private function translateFields(array $data, array $fieldDictionary): array
  {
    $translated = [];

    foreach ($data as $key => $value) {
      $translatedKey = $fieldDictionary[$key] ?? $key;

      if (is_array($value)) {
        $translated[$translatedKey] = $this->translateFields($value, $fieldDictionary);
      } else {
        $translated[$translatedKey] = $value;
      }
    }

    return $translated;
  }

  /**
   * Reordena os campos principais.
   *
   * @param array $data
   * @return array
   */
  private function reorderFields(array $data): array
  {
    $order = [
      'id',
      'titulo',
      'tipo',
      'criado_em',
      'alterado_em',
      'texto',
      'campos',
      'exportado_em',
    ];

    $ordered = [];
    foreach ($order as $field) {
      if (isset($data[$field])) {
        $ordered[$field] = $data[$field];
      }
    }

    // Adiciona quaisquer campos que não estavam na ordem definida.
    foreach ($data as $key => $value) {
      if (!array_key_exists($key, $ordered)) {
        $ordered[$key] = $value;
      }
    }

    return $ordered;
  }
}
