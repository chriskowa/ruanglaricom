<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @if(isset($url['alternates']) && is_array($url['alternates']))
        @foreach($url['alternates'] as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt['hreflang'] }}" href="{{ $alt['href'] }}" />
        @endforeach
        @endif
        @if(isset($url['lastmod']))
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        @endif
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
    @endforeach
</urlset>
