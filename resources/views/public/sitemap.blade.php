{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Core Hub Pages -->
    <url>
        <loc>{{ route('public.home') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('public.properties') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('public.land') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('public.buy') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.rent') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.services') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.services.land_survey') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc>{{ route('public.projects') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.locations') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>{{ route('public.blog') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('public.about') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>{{ route('public.contact') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>{{ route('public.faq') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Published Properties & Land Parcels -->
    @foreach ($properties as $property)
    <url>
        <loc>{{ route('public.properties.show', $property->slug) }}</loc>
        <lastmod>{{ $property->updated_at ? $property->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    @endforeach

    <!-- Real Estate Projects -->
    @foreach ($projects as $project)
    <url>
        <loc>{{ route('public.projects.show', $project->slug) }}</loc>
        <lastmod>{{ $project->updated_at ? $project->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <!-- Published Blog & Advertisement Articles -->
    @foreach ($articles as $article)
    <url>
        <loc>{{ route('public.blog.show', $article->slug) }}</loc>
        <lastmod>{{ $article->updated_at ? $article->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.75</priority>
    </url>
    @endforeach
</urlset>
