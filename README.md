Pagination rel links
=======================

This extension enables you to add next and prev rel links to paginated pages.

Simply place the following tags within the HTML head area to output the full link tag:

{pagination_rel_links:prev}

{pagination_rel_links:next}

You may also use the following tags to only output the URL and URI:

{pagination_rel_links:prev_uri}

{pagination_rel_links:next_uri}

{pagination_rel_links:prev_url}

{pagination_rel_links:next_url}

This extension uses the following hooks:

pagination_create
template_post_parse
