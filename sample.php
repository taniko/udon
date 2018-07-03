<?php
require_once __DIR__.'/vendor/autoload.php';

$filename = __DIR__.'/corpus.json';

if (file_exists($filename)) {
    $corpus = Taniko\Udon\Corpus::load($filename);
} else {
    $splFileObject = new SplFileObject(__DIR__ . '/tweets.csv');
    $splFileObject->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);

    $tweets = [];
    $flag = false;
    foreach ($splFileObject as $line) {
        if ($flag) {
            $tweets[] = $line[5];
        } else {
            $flag = true;
        }
    }

    $corpus = Taniko\Udon\Corpus::create(Taniko\Udon\Corpus::filter($tweets));
    $corpus->save($filename);
}

$markov = new Taniko\Udon\Markov($corpus);
for ($i = 0; $i < 10; $i++) {
    print $markov->text()."\n";
}