<?php
namespace Taniko\Udon;

class Corpus
{
    const SOL = ':SOL:';
    const EOL = ':EOL:';

    private $data;

    /**
     * Corpus constructor.
     * @param array $data
     */
    private function __construct(array $data)
    {
        return $this->data = $data;
    }

    /**
     * @param array $lines
     * @return Corpus
     */
    public static function create(array $lines): Corpus
    {
        $data = [];
        $mecab = new \MeCab\Tagger();
        foreach ($lines as $line) {
            $nodes = $mecab->parseToNode($line);
            $nodes = iterator_to_array($nodes);
            $words = array_values(array_filter(array_map(function ($node) {
                return $node->getSurface();
            }, $nodes)));
            $words[-1] = self::SOL;
            $words[] = self::EOL;

            foreach ($words as $i => $word) {
                $next = $i + 1;
                if (isset($words[$next])) {
                    $data[$word][$words[$next]] =
                        isset($data[$word][$words[$next]])
                            ? $data[$word][$words[$next]] + 1
                            : 1;
                }
            }
        }
        return new Corpus($data);
    }

    /**
     * @param string $filename
     * @return Corpus
     */
    public static function load(string $filename): Corpus
    {
        return new Corpus(json_decode(file_get_contents($filename), true));
    }

    /**
     * @param string $filename
     */
    public function save(string $filename)
    {
        file_put_contents($filename, json_encode($this->data));
    }

    /**
     * @return string
     */
    public function first(): string
    {
        return array_rand($this->data[self::SOL]);
    }

    /**
     * @param $word
     * @return null|string
     */
    public function next($word): ?string
    {
        $total = 0;
        $sum = array_sum($this->data[$word]);
        $rand = rand(1, $sum);
        $result = self::EOL;
        foreach($this->data[$word] as $key => $value) {
            $total += $value;
            if ($rand <= $total) {
                $result = $key;
                break;
            }
        }
        return $result === self::EOL ? null : $result;
    }

    /**
     * @param array $tweets
     * @return array
     */
    public static function filter(array $tweets): array
    {
        return array_values(array_filter(array_map('trim', preg_replace(
            '/(\@[\w]+|\#(\w+|\W)|https?:\/\/[\w-]+\.+[\w-]+\/[\w-.\/?%&=]*)/u',
            '',
            $tweets
        ))));
    }
}