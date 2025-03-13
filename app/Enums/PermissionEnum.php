<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case VIEW_BOOK = 'view_book';
    case VIEW_ANY_BOOK = 'view_any_book';
    case CREATE_BOOK = 'create_book';
    case UPDATE_BOOK = 'update_book';
    case DELETE_BOOK = 'delete_book';

    case VIEW_ROLE = 'view_role';
    case VIEW_ANY_ROLE = 'view_any_role';
    case CREATE_ROLE = 'create_role';
    case UPDATE_ROLE = 'update_role';
    case DELETE_ROLE = 'delete_role';

    case CREATE_SECTION = 'create_section';
    case EDIT_SECTION = 'edit_section';
    case DELETE_SECTION = 'delete_section';
}
