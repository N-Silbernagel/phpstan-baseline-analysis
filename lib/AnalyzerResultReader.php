<?php

namespace staabm\PHPStanBaselineAnalysis;

use function Safe\file_get_contents;
use function Safe\json_decode;

final class AnalyzerResultReader {
    /**
     * @return array<string, AnalyzerResult>
     * @throws \Safe\Exceptions\FilesystemException
     * @throws \Safe\Exceptions\JsonException
     */
    public function readFile(string $filePath): array
    {
        $json = $this->readResultArray($filePath);

        $decoded = [];
        foreach ($json as $data) {

            if (!is_array($data)) {
                throw new \RuntimeException('Expecting array, got ' . gettype($data));
            }

            foreach ($data as $baselinePath => $resultArray) {

                if (!is_string($baselinePath)) {
                    throw new \RuntimeException('Expecting string, got ' . gettype($baselinePath));
                }
                if (!is_array($resultArray)) {
                    throw new \RuntimeException('Expecting array, got ' . gettype($resultArray));
                }

                $decoded[$baselinePath] = $this->buildAnalyzerResult($resultArray);
            }
        }

        return $decoded;
    }

    /**
     * @param string $filePath
     * @return array<mixed>
     * @throws \Safe\Exceptions\FilesystemException
     * @throws \Safe\Exceptions\JsonException
     */
    private function readResultArray(string $filePath): array
    {
        \Safe\fwrite(STDERR, 'Reading file ' . $filePath . PHP_EOL);

        $content = file_get_contents($filePath);
        if ($content === '') {
            throw new \RuntimeException('File ' . $filePath . ' is empty');
        }
        $json = json_decode($content, true);
        if (!is_array($json)) {
            throw new \RuntimeException('Expecting array, got ' . get_debug_type($json));
        }

        return $json;
    }

    /**
     * @param array<mixed> $resultArray
     */
    private function buildAnalyzerResult(array $resultArray): AnalyzerResult
    {
        $result = new AnalyzerResult();
        if (array_key_exists(ResultPrinter::KEY_REFERENCE_DATE, $resultArray)) {

            $dt = \DateTimeImmutable::createFromFormat(
                ResultPrinter::DATE_FORMAT,
                $resultArray[ResultPrinter::KEY_REFERENCE_DATE]
            );
            if ($dt !== false) {
                $result->referenceDate = $dt;
            }
        }
        if (array_key_exists(ResultPrinter::KEY_OVERALL_ERRORS, $resultArray)) {
            $result->overallErrors = $resultArray[ResultPrinter::KEY_OVERALL_ERRORS];
        }
        if (array_key_exists(ResultPrinter::KEY_CLASSES_COMPLEXITY, $resultArray)) {
            $result->classesComplexity = $resultArray[ResultPrinter::KEY_CLASSES_COMPLEXITY];
        }
        if (array_key_exists(ResultPrinter::KEY_DEPRECATIONS, $resultArray)) {
            $result->deprecations = $resultArray[ResultPrinter::KEY_DEPRECATIONS];
        }
        if (array_key_exists(ResultPrinter::KEY_INVALID_PHPDOCS, $resultArray)) {
            $result->invalidPhpdocs = $resultArray[ResultPrinter::KEY_INVALID_PHPDOCS];
        }
        if (array_key_exists(ResultPrinter::KEY_UNKNOWN_TYPES, $resultArray)) {
            $result->unknownTypes = $resultArray[ResultPrinter::KEY_UNKNOWN_TYPES];
        }
        if (array_key_exists(ResultPrinter::KEY_ANONYMOUS_VARIABLES, $resultArray)) {
            $result->anonymousVariables = $resultArray[ResultPrinter::KEY_ANONYMOUS_VARIABLES];
        }
        if (array_key_exists(ResultPrinter::KEY_PROPERTY_TYPE_COVERAGE, $resultArray)) {
            $result->propertyTypeCoverage = $resultArray[ResultPrinter::KEY_PROPERTY_TYPE_COVERAGE];
        }
        if (array_key_exists(ResultPrinter::KEY_PARAM_TYPE_COVERAGE, $resultArray)) {
            $result->paramTypeCoverage = $resultArray[ResultPrinter::KEY_PARAM_TYPE_COVERAGE];
        }
        if (array_key_exists(ResultPrinter::KEY_RETURN_TYPE_COVERAGE, $resultArray)) {
            $result->returnTypeCoverage = $resultArray[ResultPrinter::KEY_RETURN_TYPE_COVERAGE];
        }
        return $result;
    }
}
