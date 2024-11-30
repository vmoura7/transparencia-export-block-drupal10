# Transparência Export Block

O módulo Transparência Export Block oferece a funcionalidade de exportar conteúdos do site em múltiplos formatos, incluindo JSON, XML e PDF, através de ícones interativos.

## Requisitos

1. O módulo utiliza a biblioteca `mPDF`. As dependências são instaladas automaticamente via Composer.
2. Certifique-se de que o diretório temporário do Drupal tem permissões adequadas para escrita. Por padrão, o mPDF usa o diretório `/tmp/mpdf`.
3. Módulos Drupal necessários:
   - Block

## Instalação

1. Baixe e copie o módulo para o diretório `modules/custom` do seu site Drupal.
2. Habilite o módulo através da interface de administração ou usando Drush:
   ```sh
   drush en transparencia_export_block -y
   ```
