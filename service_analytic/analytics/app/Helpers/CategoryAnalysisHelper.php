<?php

namespace App\Helpers;

class CategoryAnalysisHelper
{
    /**
     * Получить список категорий бренда, для которых нужно расширить список категорий.
     *
     * @param array $subjectsMap
     * @param array $subjects
     * @param $brand
     * @return string|null
     */
    public static function getReccomendationsSubjectsDecrease(array $subjectsMap, array $subjects, $brand): null|string
    {
        //находим subjects других брендов
        $outerSubjects = [];
        foreach ($subjectsMap as $brandSub => $subjectMap) {
            if ($brandSub != $brand) {
                $outerSubjects = array_merge($outerSubjects, $subjectMap);
            }
        }
        $outerSubjects = array_unique($outerSubjects);

        $subjectsDiff = array_diff($outerSubjects, $subjectsMap[$brand]);

        $result = null;

        if ($subjectsDiff) {
            $countSubjects = count($subjectsDiff);
            $subjectsDiff = array_slice($subjectsDiff, 0, 10);

            $nameSubjects = [];
            foreach ($subjectsDiff as $subjectDiff) {
                $nameSubjects[] = $subjects[$subjectDiff];
            }
            $result = implode(', ', $nameSubjects);
            if ($countSubjects > 10) {
                $result .= ' и другие';
            }

            $result = trans('category_analysis.subjects_decrease', ['subjects' => $result]);
        }

        return $result;
    }

    /**
     * Получить список категорий бренда, для которых нужно дополнить и оптимизировать контент по предмету.
     *
     * @param $brandSubjects
     * @param array $subjects
     * @param $brand
     * @param $subjectsAvgTake
     * @param string $brandName
     * @return string|null
     */
    public static function getReccomendationsSubjectContent($brandSubjects, array $subjects, $brand, $subjectsAvgTake, string $brandName): null|string
    {
        $nameSubjects = [];

        foreach ($brandSubjects as $brandSubject) {
            foreach ($subjectsAvgTake as $avgTakeBrand => $avgTakeValues) {
                if ($avgTakeBrand != $brand) {
                    if (isset($avgTakeValues[$brandSubject['subject_id']]) && $avgTakeValues[$brandSubject['subject_id']] > $brandSubject['avg_take']) {
                        $nameSubjects[] = $subjects[$brandSubject['subject_id']];
                    }
                }
            }
        }

        $result = null;
        if ($nameSubjects) {
            $result = trans('category_analysis.subject_content',
                [
                    'subjects' => implode(', ', $nameSubjects),
                    'brand' => $brandName,
                ]);
        }

        return $result;
    }
}
