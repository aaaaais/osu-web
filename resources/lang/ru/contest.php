<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

return [
    'header' => [
        'small' => 'Соревнуйтесь в чём-то, помимо нажатий по кружкам.',
        'large' => 'Конкурсы сообщества',
    ],

    'index' => [
        'nav_title' => 'библиотека',
    ],

    'voting' => [
        'login_required' => 'Пожалуйста, войдите в аккаунт, чтобы проголосовать.',
        'over' => 'Голосование на этом конкурсе окончено',
        'show_voted_only' => 'Показать проголосованные ',

        'best_of' => [
            'none_played' => "Не похоже, что Вы играли в какие-либо из карт, что квалифицированы для этого конкурса!",
        ],

        'button' => [
            'add' => 'Голосовать',
            'remove' => 'Отменить голос',
            'used_up' => 'Вы уже использовали все свои голоса',
        ],
    ],
    'entry' => [
        '_' => 'заявка',
        'login_required' => 'Войдите, чтобы участвовать в этом конкурсе.',
        'silenced_or_restricted' => 'Вы не можете участвовать в конкурсе пока ваши права ограничены.',
        'preparation' => 'В настоящее время мы готовим этот конкурс! Пожалуйста, потерпи немного.',
        'over' => 'Благодарим за ваши заявки на участие в этом конкурсе! Голосование начнется в ближайшее время.',
        'limit_reached' => 'Вы исчерпали количество заявок для этого конкурса.',
        'drop_here' => 'Брось свою заявку сюда',
        'download' => 'Скачать .osz',
        'wrong_type' => [
            'art' => 'Только файлы формата .jpg и .png разрешены для этого конкурса.',
            'beatmap' => 'Только файлы формата .osu разрешены для этого конкурса.',
            'music' => 'Только файлы формата .mp3 разрешены для этого конкурса.',
        ],
        'too_big' => 'Размеры файлов для этого конкурса не могут превышать :limit.',
    ],
    'beatmaps' => [
        'download' => 'Скачать файлы',
    ],
    'vote' => [
        'list' => 'голосов',
        'count' => ':count голос|:count голоса|:count голосов',
        'points' => ':count очко|:count очка|:count очков',
    ],
    'dates' => [
        'ended' => 'Окончено :date',
        'ended_no_date' => 'Окончен',

        'starts' => [
            '_' => 'Начнётся :date',
            'soon' => 'скоро™',
        ],
    ],
    'states' => [
        'entry' => 'Заявки открыты',
        'voting' => 'Голосование началось',
        'results' => 'Результаты опубликованы',
    ],
];
