<?php
namespace Plah;

class Pagination extends Singleton
{
    /**
     * Paginate the given numbers.
     * This will return an array with the first and
     * the last page, previous and next page, pages to
     * show, the active page, the number of entries on
     * the active page and the total number of entries.
     * Non of the numbers will be less than 1 to avoid
     * division by zero problems. As well no numbers
     * less than 1 or non-int will be accepted for
     * the calculations.
     *
     * @param int $total
     * @param int $entries
     * @param int $page
     * @param int $pages
     * @return array
     */
    public function get($total, $entries, $page, $pages = 1)
    {
        $data = array(
            'first' => 1,
            'last' => 1,
            'previous' => 1,
            'next' => 1,
            'pages' => array(1),
            'active' => 1,
            'entries' => 1,
            'total' => 1,
            'start' => 1,
            'end' => 1
        );

        $total = intval($total);
        $entries = intval($entries);
        $page = intval($page);
        $pages = intval($pages);

        if ($total > 0 && $entries > 0 && $page > 0 && $pages > 0) {
            //Calculate the basic values
            $data['first'] = 1;
            $data['last'] = ceil($total / $entries);
            $data['active'] = ($page <= $data['last']) ? $page : $data['last'];
            $data['previous'] = ($data['active'] > $data['first']) ? $data['active'] - 1 : $data['first'];
            $data['next'] = ($data['active'] < $data['last']) ? $data['active'] + 1 : $data['last'];
            $data['entries'] = ($data['active'] == $data['last']) ? $total - (($data['last'] - 1) * $entries) : $entries;
            $data['total'] = $total;
            $data['start'] = (($data['active'] - 1) * $entries) + 1;
            $data['end'] = (($data['active'] - 1) * $entries) + $data['entries'];

            //Calculate the pages, try to get the active page to the middle position
            $right_limit = $data['last'] - $data['active'];  //Max possible steps right from active page
            $left_limit = $data['active'] - $data['first'];  //Max possible steps left from active page
            $right = floor($pages / 2);  //Necessary steps right from active page
            $left = $pages - $right - 1;  //Necessary steps left from active page
            if ($right > $right_limit) {  //Correct right steps if over the limit
                $left += $right - $right_limit;
                $right = $right_limit;
            }
            if ($left > $left_limit) {  //Correct left steps if over the limit
                $right += $left - $left_limit;
                $left = $left_limit;
            }
            if ($right > $right_limit) {  //Correct right steps if over the limit again
                $right = $right_limit;
            }
            $a = $data['active'] - $left;  //Calculate start page
            $b = $data['active'] + $right;  //Calculate end page
            $data['pages'] = range($a, $b);
        }

        return $data;
    }
}
