<?php

    namespace MPRating\Data;

    defined( 'ABSPATH' ) || exit;

    abstract class Statistic_Query
    {
        protected string $selected_period;

        protected array $date_ranges;

        public function __construct($selected_period = '')
        {
            $this->selected_period = $selected_period;
            $this->date_ranges = $this->prepare_dates_array($this->selected_period);
        }

        abstract protected function prepare_dates_array($period);

        abstract public function get_totals($post_type);
    }