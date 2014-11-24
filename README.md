Pagination Rel Links
=======================

For ExpressionEngine 2

This extension enables you to add next and prev rel links to paginated pages.
This creates HTML link elements with rel="next" and rel="prev" to indicate the relationship between component URLs in a paginated series.
See: http://googlewebmastercentral.blogspot.co.uk/2011/09/pagination-with-relnext-and-relprev.html

Simply place the following tags within the HTML head area to output the full link tag:
--------------------------------------------------------------------------------------

{pagination_rel_links:prev}
{pagination_rel_links:next}

An example output would be:

`<link rel="prev" href="http://www.example.com/articles/P10" />`
`<link rel="next" href="http://www.example.com/articles/P30" />`

You may also use the following tags to only output the URL and URI:
-------------------------------------------------------------------

{pagination_rel_links:prev_uri}
{pagination_rel_links:next_uri}
{pagination_rel_links:prev_url}
{pagination_rel_links:next_url}

This extension uses the following hooks:

pagination_create
template_post_parse
