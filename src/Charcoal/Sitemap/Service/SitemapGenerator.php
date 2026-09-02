<?php

namespace Charcoal\Sitemap\Service;

/**
 * Generates a sitemap XML document from a configured hierarchy.
 *
 * Uses the "xml" hierarchy ident by default to match the sitemap builder
 * configuration and the public `/sitemap.xml` route.
 */
class SitemapGenerator
{
    protected Builder $sitemapBuilder;

    protected XmlFormatter $xmlFormatter;

    /**
     * The sitemap hierarchy to build.
     */
    protected string $sitemapIdent;

    /**
     * @param Builder      $sitemapBuilder The sitemap builder.
     * @param XmlFormatter $xmlFormatter   The XML formatter.
     * @param string       $sitemapIdent   The hierarchy ident to build.
     */
    public function __construct(
        Builder $sitemapBuilder,
        XmlFormatter $xmlFormatter,
        string $sitemapIdent = 'xml'
    ) {
        $this->sitemapBuilder = $sitemapBuilder;
        $this->xmlFormatter   = $xmlFormatter;
        $this->sitemapIdent   = $sitemapIdent;
    }

    /**
     * Generates the sitemap XML.
     *
     * @return string|null The XML document, or null if generation failed.
     */
    public function generate(): ?string
    {
        $collections = $this->sitemapBuilder->build($this->sitemapIdent);

        return $this->xmlFormatter->createXmlFromCollections($collections);
    }
}
