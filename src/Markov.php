<?php
namespace Taniko\Udon;

class Markov
{
    private $corpus;

    /**
     * Markov constructor.
     * @param Corpus $corpus
     */
    public function __construct(Corpus $corpus)
    {
        $this->corpus = $corpus;
    }

    /**
     * @param int $limit
     * @return string
     */
    public function text(int $limit = 100): string
    {
        $word = $this->corpus->first();
        $text = $word;
        for ($i = 0; $i < $limit; $i++) {
            $word = $this->corpus->next($word);
            if (isset($word)) {
                $text .= $word;
            } else {
                break;
            }
        }
        return $text;
    }
}