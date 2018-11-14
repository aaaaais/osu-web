<?php

/**
 *    Copyright 2015-2018 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Libraries;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;

class CommentBundle
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 50;
    const DEFAULT_SORT = 'new';

    const SORTS = [
        'new' => ['order' => 'DESC', 'columns' => ['created_at', 'id']],
        'old' => ['order' => 'ASC', 'columns' => ['created_at', 'id']],
        'top' => ['order' => 'DESC', 'columns' => ['votes_count_cache', 'created_at', 'id']],
    ];

    public $depth;
    public $includeCommentableMeta;
    public $includeParent;
    public $params;

    private $commentable;
    private $comments;
    private $lastLoadedId;
    private $user;

    public function __construct($commentable, $options = [])
    {
        $this->commentable = $commentable;

        $this->params = new CommentBundleParams($options['params'] ?? []);

        $this->comments = $options['comments'] ?? null;
        $this->additionalComments = $options['additionalComments'] ?? [];
        $this->depth = $options['depth'] ?? 2;
        $this->includeCommentableMeta = $options['includeCommentableMeta'] ?? false;
        $this->includeParent = $options['includeParent'] ?? false;
        $this->user = $options['user'] ?? auth()->user();
    }

    public function toArray()
    {
        $hasMore = false;

        if (isset($this->comments)) {
            $comments = $this->comments;
        } else {
            $comments = $this->getComments($this->commentsQuery(), false);

            if ($comments->count() > $this->params->limit) {
                $hasMore = true;
                $comments->pop();
            }

            $nestedParentIds = $comments->pluck('id');

            for ($i = 0; $i < $this->depth; $i++) {
                $ids = $nestedParentIds->toArray();
                sort($ids);
                \Log::info([
                    'depth' => $i,
                    'ids' => implode(' ', $ids),
                ]);
                $nestedComments = $this->getComments(Comment::whereIn('parent_id', $nestedParentIds));
                $nestedParentIds = $nestedComments->pluck('id');
                $comments = $comments->concat($nestedComments);
            }
        }

        $comments = $comments->concat($this->additionalComments);

        $result = [
            'comments' => json_collection($comments, 'Comment', $this->commentIncludes()),
            'has_more' => $hasMore,
            'has_more_id' => $this->params->parentId,
            'user_votes' => $this->getUserVotes($comments),
            'users' => json_collection($this->getUsers($comments), 'UserCompact'),
        ];

        if ($this->params->parentId === 0 || $this->params->parentId === null) {
            $result['top_level_count'] = $this->commentsQuery()->whereNull('parent_id')->count();
        }

        if ($this->includeCommentableMeta) {
            $commentables = $comments->pluck('commentable')->concat([null]);
            $result['commentable_meta'] = json_collection($commentables, 'CommentableMeta');
        }

        return $result;
    }

    public function commentIncludes()
    {
        $includes = [];

        if ($this->includeParent) {
            $includes[] = 'parent';
        }

        return $includes;
    }

    public function commentsQuery()
    {
        if (isset($this->commentable)) {
            return $this->commentable->comments();
        } else {
            return Comment::select();
        }
    }

    private function getComments($query, $isChildren = true)
    {
        $sort = $this->params->sortDbOptions();
        $queryLimit = $this->params->limit;

        if (!$isChildren) {
            if ($this->params->filterByParentId()) {
                $query->where(['parent_id' => $this->params->parentIdForWhere()]);
            }

            $queryLimit++;
            $values = [];

            foreach ($sort['columns'] as $column) {
                $key = $column === 'votes_count_cache' ? 'votesCount' : camel_case($column);
                $value = $this->params->cursor[$key];
                if (isset($value)) {
                    $values[$column] = $value;
                }
            }

            if (count($values) > 0 && count($values) === count($sort['columns'])) {
                $direction = $sort['order'] === 'DESC' ? '<' : '>';
                $key = implode(',', $sort['columns']);
                foreach ($values as $value) {
                    $bindingKeys[] = '?';
                    $bindingValues[] = $value;
                }
                $bindingKey = implode(',', $bindingKeys);

                $query->whereRaw("({$key}) {$direction} ({$bindingKey})", $bindingValues);
            } else {
                $query->offset($this->params->limit * ($this->params->page - 1));
            }
        }

        if ($this->includeCommentableMeta) {
            $query->with('commentable');
        }

        if ($this->includeParent) {
            $query->with('parent');
        }

        foreach ($sort['columns'] as $column) {
            $query->orderBy($column, $sort['order']);
        }

        return $query->limit($queryLimit)->get();
    }

    private function getUserVotes($comments)
    {
        if ($this->user === null) {
            return [];
        }

        $ids = $comments->pluck('id');

        return CommentVote::where(['user_id' => $this->user->getKey()])
            ->whereIn('comment_id', $ids)
            ->pluck('comment_id');
    }

    private function getUsers($comments)
    {
        $userIds = $comments->pluck('user_id')
            ->concat($comments->pluck('edited_by_id'));

        if ($this->includeParent) {
            foreach ($comments as $comment) {
                if ($comment->parent !== null) {
                    $userIds[] = $comment->parent->user_id;
                }
            }
        }

        return User::whereIn('user_id', $userIds)->get();
    }
}
