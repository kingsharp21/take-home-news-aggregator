<?php

namespace App\Http\Controllers;
use App\Models\Source;
use App\Models\Category;
use App\Models\Article;

use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function getSources () {
        try {
            $sources = Source::all();
            return response()->json($sources, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sources'], 500);
        }
    }

    public function getCategories () {
        try {
            $category = Category::all();
            return response()->json($category, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve sources'], 500);
        }
    }


    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $category = $request->input('category');
        $source = $request->input('source');
        
        $query = Article::query();

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('content', 'like', '%' . $keyword . '%');
            });
        }
        if ($startDate) {
            $query->whereDate('published_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('published_at', '<=', $endDate);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($source) {
            $query->where('source', $source);
        }
        
        $articles = $query->get();

        return response()->json($articles, 200);
    }

    public function preferenceSearch(Request $request)
    {

        $category = $request->input('category');
        $source = $request->input('source');
        
        $query = Article::query();

        if ($category) {
            $query->where('category', $category);
        }

        if ($source) {
            $query->where('source', $source);
        }

        $articles = $query->get();

        return response()->json($articles, 200);
    }

}
