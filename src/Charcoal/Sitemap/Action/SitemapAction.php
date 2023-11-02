<?php

namespace Charcoal\Sitemap\Action;

use Charcoal\App\Action\AbstractAction;
use Charcoal\Sitemap\Service\Builder;
use Charcoal\Sitemap\Service\XmlFormatter;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SitemapAction extends AbstractAction
{
    /**
     * The sitemap hierarchy to output.
     *
     * @var string|null
     */
    protected $sitemapIdent;

    /**
     * The sitemap XML as a string.
     *
     * @var string|null
     */
    protected $sitemapXml;

    /**
     * The sitemap builder.
     *
     * @var Builder|null
     */
    protected $sitemapBuilder;

    /**
     * The XML formatter.
     *
     * @var XmlFormatter|null
     */
    protected $xmlFormatter;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->sitemapBuilder = $container['charcoal/sitemap/builder'];
        $this->xmlFormatter   = $container['sitemap/formatter/xml'];
    }

    /**
     * Returns an HTTP response with the sitemap XML.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $this->setMode(self::MODE_XML);

        $collections = $this->sitemapBuilder->build($this->sitemapIdent ?? 'xml');
        $this->sitemapXml = $this->xmlFormatter->createXmlFromCollections($collections);

        $this->setSuccess(true);

        return $response;
    }

    /**
     * The XML string.
     *
     * @return string|null
     */
    public function results()
    {
        return $this->sitemapXml;
    }
}
