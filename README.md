# SiteMapMirror

This is a *very* simple web site copier. It uses a given XML sitemap as a starting point 
to download all the pages and their assets. It does *no* rewriting of URLs so URLs need 
to be relative or server absolute.

Run `composer install` to fetch the requirments, then use the commandline:

    ./smm.php -d download https://example.com/sitemap.xml 
