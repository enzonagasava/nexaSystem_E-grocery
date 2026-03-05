<?php
use Carbon\Carbon;

if (!function_exists('formatDate')) {
    /**
     * Formata uma data em formato legível (padrão brasileiro).
     *
     * @param string|\DateTimeInterface|null $date
     * @param string $format
     * @return string
     */
    function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (empty($date)) {
            return '-';
        }

        return Carbon::parse($date)
            ->timezone('America/Sao_Paulo')
            ->format($format);
    }
}
