<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Property;
use App\Models\RealEstateProject;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap for Google and search engines.
     */
    public function index(): Response
    {
        $properties = Property::where('is_published', true)->latest()->get();
        $projects = RealEstateProject::where('is_published', true)->latest()->get();
        $articles = Article::where('is_published', true)->latest('published_at')->get();

        $content = view('public.sitemap', compact('properties', 'projects', 'articles'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }
}
