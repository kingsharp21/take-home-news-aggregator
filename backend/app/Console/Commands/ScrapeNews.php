<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Carbon\Carbon;
use GuzzleHttp\Client;

class ScrapeNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

     protected $categoryMap = [
        'politics' => 'Politics',
        'sports' => 'Sports',
        'technology' => 'Technology',
        'health' => 'Health',
        'business' => 'Business',
    ];

    protected $categoryKeywords = [
        'Technology' => ['technology', 'tech', 'gadgets', 'innovation'],
        'Politics' => ['politics', 'government', 'election', 'policy'],
        'Sports' => ['sports', 'football', 'basketball', 'cricket'],
        'Health' => ['health', 'wellness', 'medicine', 'fitness'],
        'Business' => ['business', 'finance', 'economy', 'market'],
    ];

    


    public function handle()
    {
        $client = new Client();
        
        // Fetch articles from NewsAPI
        $this->scrapeNewsAPI($client);
        
        // Fetch articles from GuardianAPI
        $this->scrapeGuardianAPI($client);

        // Fetch articles from NytimesAPI
        $this->scrapeNytimesAPI($client);

        $this->info('News articles have been scraped and stored successfully.');
    }


    protected function scrapeNewsAPI(Client $client)
    {
        $response = $client->get('https://newsapi.org/v2/everything', [
            'query' => [
                'q' => 'latest',
                'apiKey' => 'fe7580ea12184607809423f3682118f4'
            ]
        ]);

        $articles = json_decode($response->getBody()->getContents(), true)['articles'];

        foreach ($articles as $article) {
            if ($article['author']) {
                # code...
                $sourceCategory = $this->getSourceCategory($article);
                $mappedCategory = $this->categoryMap[strtolower($sourceCategory)] ?? 'General';
    
                $category = Category::firstOrCreate(['name' => $mappedCategory]);

                $source = Source::firstOrCreate(['name' => $article['source']['name']]);
    
    
                $publishedAt = $this->parseDate($article['publishedAt']); 
    
                if ($publishedAt === false) {
                    $publishedAt = null; 
                }
    
                Article::create([
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'author' => $article['author'],
                    'source' => $source->id,
                    'url' => $article['url'],
                    'urlToImage' => $article['urlToImage'],
                    'category' => $category->id,
                    'published_at' => $publishedAt,
                ]);
            }

        }
    }

    protected function scrapeGuardianAPI(Client $client)
    {
        $response = $client->get('https://content.guardianapis.com/search', [
            'query' => [
                'q' => 'latest',
                'format' => 'json',
                'page-size' => 200,
                'order-by' => 'relevance',
                'api-key' => 'test',
                'show-tags' => 'contributor',
                'show-fields' => 'starRating,headline,thumbnail,standfirst,publication,short-url,trailText'
            ]
        ]);

        $articles = json_decode($response->getBody()->getContents(), true)['response']['results'];

        foreach ($articles as $article) {
                # code...
    
                $category = Category::firstOrCreate(['name' => $article['sectionId']]);
    

                $source = Source::firstOrCreate(['name' => $article['fields']['publication']]);
    
                $publishedAt = $this->parseDate($article['webPublicationDate']); 
    
                if ($publishedAt === false) {
                    $publishedAt = null; 
                }



                
    
                Article::create([
                    'title' => $article['webTitle'],
                    'content' => $article['fields']['trailText'],
                    'author' => $article['tags'][0]['webTitle'] ?? 'null',
                    'source' => $source->id,
                    'url' => $article['webUrl'],
                    'urlToImage' => $article['fields']['thumbnail'],
                    'category' => $category->id,
                    'published_at' => $publishedAt,
                ]);

        }
    }


    protected function scrapeNytimesAPI(Client $client)
    {
        $response = $client->get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
            'query' => [
                'q' => 'latest',
                'page' => 1,
                'api-key' => 'qhjIoG09y3Aagg9CjdH4ieoDKxEa0PzZ',
            ]
        ]);

        $articles = json_decode($response->getBody()->getContents(), true)['response']['docs'];

        foreach ($articles as $article) {
                # code...
    
                $sourceCategory = $this->getSourceCategory2($article);
                $mappedCategory = $this->categoryMap[strtolower($sourceCategory)] ?? 'General';
    
                $category = Category::firstOrCreate(['name' => $mappedCategory]);
    
                $source = Source::firstOrCreate(['name' => $article['source']]);
    
                $publishedAt = $this->parseDate($article['pub_date']); 
    
    
                if ($publishedAt === false) {
                    $publishedAt = null; 
                }

                $image = !empty($article['multimedia']) && !empty($article['multimedia'][1]['url'])
                ? $article['multimedia'][1]['url']
                : 'null';
    
                Article::create([
                    'title' => $article['headline']['main'],
                    'content' => $article['lead_paragraph'],
                    'author' => $article['byline']['original'] ?? 'null',
                    'source' => $source->id,
                    'url' => $article['web_url'],
                    'urlToImage' => 'https://www.nytimes.com/'.$image ,
                    'category' => $category->id,
                    'published_at' => $publishedAt,
                ]);

        }
    }

    protected function getSourceCategory(array $article)
    {
        $title = strtolower($article['title']);
        $content = strtolower($article['content']);
    
        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($title, $keyword) !== false || strpos($content, $keyword) !== false) {
                    return $category;
                }
            }
        }
    
        return 'General';
    }

    protected function getSourceCategory2(array $article)
    {
        $title = strtolower($article['headline']['main']);
        $content = strtolower($article['lead_paragraph']);
    
        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($title, $keyword) !== false || strpos($content, $keyword) !== false) {
                    return $category;
                }
            }
        }
    
        return 'General'; 
    }

    private function parseDate($date)
    {
        try {
            return (new \DateTime($date))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return false;
        }
    }

}
