<?php

namespace App\Services;

/**
 * ML Classification and Recommendation module (SDD 5.8).
 *
 * In production this calls out to an external ML microservice that runs
 * text vectorization and a trained classifier over the topic title/body.
 * Here it is implemented as a lightweight keyword-based fallback so the
 * platform still assigns a sensible category with zero external
 * dependencies; swap classify() for an HTTP call to the ML service when
 * one is available.
 */
class TopicClassifierService
{
    private const CATEGORY_KEYWORDS = [
        'Networking' => ['network', 'tcp', 'ip', 'router', 'protocol'],
        'Databases' => ['database', 'sql', 'query', 'schema', 'index'],
        'Programming' => ['code', 'function', 'bug', 'compile', 'algorithm'],
        'Machine Learning' => ['model', 'training', 'classifier', 'dataset', 'neural'],
        'Software Design' => ['architecture', 'design', 'uml', 'pattern', 'module'],
    ];

    public function classify(string $title): string
    {
        $lower = strtolower($title);

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $category;
                }
            }
        }

        return 'General';
    }

    /**
     * Content-based + collaborative relevance score in [0, 1] for a given
     * user/topic pairing. Placeholder scoring based on shared category
     * interest; replace with a real recommender when available.
     */
    public function relevanceScore(int $matchingCategoryCount, int $totalUserTopics): float
    {
        if ($totalUserTopics === 0) {
            return 0.5;
        }

        return round(min(1, $matchingCategoryCount / $totalUserTopics), 3);
    }
}
