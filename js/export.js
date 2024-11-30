(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exportBlock = {
    attach: function (context, settings) {
      // Exportar JSON
      once('export-json', '#export-json-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportNode('json');
        });
      });

      // Exportar XML
      once('export-xml', '#export-xml-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportNode('xml');
        });
      });

      // Exportar PDF
      once('export-pdf', '#export-pdf-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportNode('pdf');
        });
      });

      // Imprimir página
      once('print-page', '#print-button', context).forEach((element) => {
        $(element).on('click', function () {
          window.print(); // Chama a função de impressão do navegador.
        });
      });

      // Função genérica para exportar o nó.
      function exportNode(format) {
        const currentPath = drupalSettings.path.currentPath;
        const nid = currentPath.split('/').pop();

        if (isNaN(nid)) {
          alert('O conteúdo atual não é um nó válido.');
          return;
        }

        fetch(`/transparencia/export/${nid}/${format}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Falha ao exportar dados no formato ${format}.`);
            }
            return response.blob();
          })
          .then((blob) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `node_${nid}.${format}`;
            a.click();
            URL.revokeObjectURL(url);
          })
          .catch((error) => {
            console.error(`Erro ao exportar no formato ${format}:`, error);
            alert(`Erro ao exportar os dados em ${format}.`);
          });
      }
    },
  };
})(jQuery, Drupal, drupalSettings);
