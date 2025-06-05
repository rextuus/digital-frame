<?php

namespace App\Service\Scheduling;

enum ScheduleAction: string
{
    case DEFAULT = 'default';
    case SHOW_RANDOM_DISPLATE_FROM_SEARCH_TAG = 'show_random_displate_from_search_tag';
    case SHOW_RANDOM_FAVORITE_FROM_LIST = 'show_random_favorite_from_list';
}
