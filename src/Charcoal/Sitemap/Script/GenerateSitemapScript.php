<?php

namespace Charcoal\Sitemap\Script;

use Charcoal\App\Script\AbstractScript;
use Charcoal\Sitemap\Service\SitemapGenerator;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * CLI script to write a static `sitemap.xml` file.
 *
 * Invoked as `vendor/bin/charcoal sitemap/generate`.
 */
class GenerateSitemapScript extends AbstractScript
{
    protected SitemapGenerator $sitemapGenerator;

    /**
     * Application base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->sitemapGenerator = $container['charcoal/sitemap/generator'];
        $this->basePath         = $container['config']['base_path'] ?? getcwd();

        parent::setDependencies($container);
    }

    /**
     * Generates and writes the sitemap XML file.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     * @throws RuntimeException If the sitemap file cannot be written.
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $xml = $this->sitemapGenerator->generate();

        if (!empty($xml)) {
            $sitemapPath = rtrim($this->basePath, '/\\')
                . DIRECTORY_SEPARATOR . 'www'
                . DIRECTORY_SEPARATOR . 'sitemap.xml';

            if (file_put_contents($sitemapPath, $xml, LOCK_EX) === false) {
                throw new RuntimeException(sprintf(
                    'Unable to write sitemap to "%s".',
                    $sitemapPath
                ));
            }
        }

        return $response;
    }
}
