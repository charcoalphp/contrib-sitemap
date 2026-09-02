$(document).ready(function () {
    $(document).on('click', '#btn-generate-sitemap', function (e) {
        e.preventDefault();

        var $button = $(this);
        var url = $button.data('url') || $button.attr('href');

        if (!url || $button.prop('disabled')) {
            return;
        }

        var originalHtml = $button.html();
        var loadingHtml = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:6px;"></span>Syncing...';

        $button
            .prop('disabled', true)
            .addClass('disabled')
            .html(loadingHtml);

        fetch(url, {
            method: 'GET'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    Charcoal.Admin.feedback([ {
                        level:   'error',
                        message: 'Une erreur est survenue pendant la génération du sitemap. Veuillez réessayer.',
                    } ]);
                } else {
                    Charcoal.Admin.feedback([ {
                        level:   'warning',
                        title:   'Génération du sitemap',
                        message: 'Le sitemap a été généré avec succès.',
                    } ]);
                }
            })
            .catch(function (err) {
                Charcoal.Admin.feedback([ {
                    level:   'error',
                    message: 'Une erreur est survenue pendant la génération du sitemap. Veuillez réessayer.',
                } ]);

                console.error('Request failed:', err);
            })
            .finally(function () {
                $button
                    .prop('disabled', false)
                    .removeClass('disabled')
                    .html(originalHtml);

                Charcoal.Admin.feedback().dispatch();
            });
    });
});
