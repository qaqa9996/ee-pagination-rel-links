Pagination Rel Links
=======================

For ExpressionEngine 2

This extension enables you to add next and prev rel links to paginated pages.
This creates HTML link elements with rel="next" and rel="prev" to indicate the relationship between component URLs in a paginated series.
See: http://googlewebmastercentral.blogspot.co.uk/2011/09/pagination-with-relnext-and-relprev.html

To activate this extension, go to Addons > Extensions and click Install.
Once installed you can change these settings:

- Enable redirect to first page where pagination is not found?
- Page number prefix: add a prefix to page numbers in titles
- Page number suffix: add a suffix to page numbers in titles
- Display first page number in titles? i.e. Omit Page #1 in {pagination_rel_links:page_num} tag


Simply place the following tags within the HTML head area to output the full link tag:
--------------------------------------------------------------------------------------

{pagination_rel_links:prev}
{pagination_rel_links:next}

An example output would be:

`<link rel="prev" href="http://www.example.com/articles/P10" />`
`<link rel="next" href="http://www.example.com/articles/P30" />`

Note: These tags can also be used in NSM Better Meta settings.


You may also use the following tags to only output the URL and URI:
-------------------------------------------------------------------

Outputs full link tags: <link rel="next" href="http://www.example.com/articles/P30" />
{pagination_rel_links:prev}
{pagination_rel_links:next}

Outputs pagination URI - Example: articles/P30
{pagination_rel_links:prev_uri}
{pagination_rel_links:next_uri}

Outputs pagination URL: http://www.example.com/articles/P30
{pagination_rel_links:prev_url}
{pagination_rel_links:next_url}

Displays the page number for use in browser titles and elsewhere:
{pagination_rel_links:page_num}

This extension uses the following hooks:

pagination_create
template_post_parse
