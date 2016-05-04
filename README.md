# PHP serializers benchmark

```
composer install
./vendor/bin/phpbench run --report=aggregate
```

# Results

- Intel Core i7-4790 @ 3.6GHz,
- 16GiB RAM,
- 250GiB SSD.

```
PhpBench 0.11-dev (@git_sha@). Running benchmarks.
Using configuration file: /var/www/SerializerBenchmark/phpbench.json

\Thunder\SerializerBenchmark\Benchmark\SerializerBench

    benchJmsCustom                I4 P0 	[μ Mo]/r: 276.654 276.332 (ms) 	[μSD μRSD]/r: 1.787ms 0.65%
    benchJmsDefault               I4 P0 	[μ Mo]/r: 727.890 720.210 (ms) 	[μSD μRSD]/r: 16.339ms 2.24%
    benchSymfonyObjectNormalizer  I4 P0 	[μ Mo]/r: 585.720 584.269 (ms) 	[μSD μRSD]/r: 4.261ms 0.73%
    benchSymfonyPropertyNormalizerI4 P0 	[μ Mo]/r: 214.489 213.791 (ms) 	[μSD μRSD]/r: 1.382ms 0.64%
    benchSymfonyGetSetNormalizer  I4 P0 	[μ Mo]/r: 388.845 384.165 (ms) 	[μSD μRSD]/r: 9.520ms 2.45%
    benchSerializardReflection    I4 P0 	[μ Mo]/r: 237.219 234.628 (ms) 	[μSD μRSD]/r: 4.032ms 1.70%
    benchSerializardClosure       I4 P0 	[μ Mo]/r: 119.786 118.645 (ms) 	[μSD μRSD]/r: 2.305ms 1.92%

7 subjects, 35 iterations, 70 revs, 0 rejects
(best [mean mode] worst) = 118.326 [364.372 361.720] 124.379 (ms)
⅀T: 12,753.007ms μSD/r 5.661ms μRSD/r: 1.476%
benchmark: SerializerBench
+--------------------------------+-------------+---------------+---------+
| subject                        | mem         | mean          | diff    |
+--------------------------------+-------------+---------------+---------+
| benchJmsCustom                 | 6,929,712b  | 276,653.780μs | +56.70% |
| benchJmsDefault                | 13,859,547b | 727,889.620μs | +83.54% |
| benchSymfonyObjectNormalizer   | 12,692,440b | 585,720.140μs | +79.55% |
| benchSymfonyPropertyNormalizer | 16,727,186b | 214,488.560μs | +44.15% |
| benchSymfonyGetSetNormalizer   | 13,943,019b | 388,844.660μs | +69.19% |
| benchSerializardReflection     | 16,696,261b | 237,219.040μs | +49.50% |
| benchSerializardClosure        | 10,847,776b | 119,785.500μs | 0.00%   |
+--------------------------------+-------------+---------------+---------+
```
