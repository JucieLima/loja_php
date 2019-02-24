<?php

namespace App\Helpers;

class Pager {

    private $page;
    private $pages;
    private $limit;
    private $offset;
    private $link;

    /** opcionais * */
    private $maxLinks;
    private $prev;
    private $next;
    private $params;

    /** renderiza * */
    private $pagination;

    function __construct($page, $pages, $limit, $offset, $link, $params = null, $maxLinks = null, $prev = null, $next = null) {
        $this->page = $page;
        $this->pages = $pages;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->link = (string) $link;
        $this->maxLinks = (int) $maxLinks ? $maxLinks : 3;
        $this->prev = (string) $prev ? $prev : 'Anterior';
        $this->next = (string) $next ? $next : 'Próxima';
        $this->params = $params ? $params : '';
        if ($this->page > $this->pages):
            $this->returnPage();
        endif;
        $this->getSyntax();
    }

    public function showPager() {
        return $this->pagination;
    }

    private function returnPage() {
        if ($this->page > 1):
            echo '<script>';
            echo 'window.location.assign("' . BASE_URL . $this->link . '/' . $this->pages . '");';
            echo '</script>';
        endif;
    }

    private function getSyntax() {
        if ($this->pages > 1):
            $previous = $this->page - 1 <= 0 ? ' disabled' : '';
            $next = $this->page >= $this->pages ? ' disabled' : '';
            $this->pagination = '<nav>';
            $this->pagination .= '<ul class="pagination justify-content-center">';
            $this->pagination .= '<ul class="pagination justify-content-center">';
            $this->pagination .= '<li class="page-item ' . $previous . '">';
            $this->pagination .= '<a class="page-link"  tabindex="-1" aria-disabled="true" href="' . BASE_URL . $this->link . '/' . ($this->page - 1) . '/' . $this->params . '">';
            $this->pagination .= $this->prev;
            $this->pagination .= '</a></li>';
            $this->getPages();
            $this->pagination .= '<li class="page-item ' . $next . '">';
            $this->pagination .= '<a class="page-link" tabindex="-1" aria-disabled="true" href="' . BASE_URL . $this->link . '/' . ($this->page + 1) . '/' . $this->params . '">';
            $this->pagination .= $this->next;
            $this->pagination .= '</a></li>';
            $this->pagination .= '</ul>';
            $this->pagination .= '</nav>';
        endif;
    }

    private function getPages() {
        for ($i = $this->page - $this->maxLinks; $i <= $this->page - 1; $i++):
            if ($i >= 1):
                $this->pagination .= '<li class="page-item"><a class="page-link" href="' . BASE_URL . $this->link . '/' . $i . '/' . $this->params . '">' . $i . '</a></li>';
            endif;
        endfor;
        $this->pagination .= '<li class="page-item active"><a class="page-link" href="' . BASE_URL . $this->link . '/' . $this->page . '/' . $this->params . '">' . $this->page . '</a></li>';
        for ($d = $this->page + 1; $d <= $this->page + $this->maxLinks; $d++):
            if ($d <= $this->pages):
                $this->pagination .= '<li class="page-item"><a class="page-link" href="' . BASE_URL . $this->link . '/' . $d . '/' . $this->params . '">' . $d . '</a></li>';
            endif;
        endfor;
    }

}
