SET NAMES utf8;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `allowed_staffs` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT '1',
  `can_edit` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `attach_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `format` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` double UNSIGNED DEFAULT NULL,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `chat_receivers` (
  `id` int(10) UNSIGNED NOT NULL,
  `chat_sender_id` int(10) UNSIGNED NOT NULL,
  `chat_room_member_id` int(10) UNSIGNED NOT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `chat_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('dedicated','shared') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dedicated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `chat_room_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `chat_room_id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` enum('staff') COLLATE utf8_unicode_ci NOT NULL,
  `is_typing` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `chat_senders` (
  `id` int(10) UNSIGNED NOT NULL,
  `chat_room_member_id` int(10) UNSIGNED NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `announcement` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_owner` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `priority` enum('high','highest','low','lowest','normal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `access` enum('private','public','public_rwd') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `linked_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linked_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `event_attendees` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` enum('staff') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','going','may_be','decline') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `filter_views` (
  `id` int(10) UNSIGNED NOT NULL,
  `module_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `view_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `filter_params` text COLLATE utf8_unicode_ci,
  `visible_type` enum('only_me','everyone','selected_users') COLLATE utf8_unicode_ci NOT NULL,
  `visible_to` text COLLATE utf8_unicode_ci,
  `is_fixed` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `filter_views` (`id`, `module_name`, `view_name`, `filter_params`, `visible_type`, `visible_to`, `is_fixed`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'task', 'My Open Tasks', '{\"task_owner\":{\"condition\":\"equal\",\"value\":[0]},\"completion_percentage\":{\"condition\":\"less\",\"value\":100},\"task_status_id\":{\"condition\":\"not_equal\",\"value\":[5]}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(2, 'task', 'My Overdue Tasks', '{\"task_owner\":{\"condition\":\"equal\",\"value\":[0]},\"completion_percentage\":{\"condition\":\"less\",\"value\":100},\"task_status_id\":{\"condition\":\"not_equal\",\"value\":[5]},\"due_date\":{\"condition\":\"last\",\"value\":90}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(3, 'task', 'My Closed Tasks', '{\"task_owner\":{\"condition\":\"equal\",\"value\":[0]},\"completion_percentage\":{\"condition\":\"equal\",\"value\":100},\"task_status_id\":{\"condition\":\"equal\",\"value\":[5]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(4, 'task', 'All Tasks', NULL, 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(5, 'task', 'Open Tasks', '{\"completion_percentage\":{\"condition\":\"less\",\"value\":100},\"task_status_id\":{\"condition\":\"not_equal\",\"value\":[5]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(6, 'task', 'Overdue Tasks', '{\"completion_percentage\":{\"condition\":\"less\",\"value\":100},\"task_status_id\":{\"condition\":\"not_equal\",\"value\":[5]},\"due_date\":{\"condition\":\"last\",\"value\":90}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(7, 'task', 'Closed Tasks', '{\"completion_percentage\":{\"condition\":\"equal\",\"value\":100},\"task_status_id\":{\"condition\":\"equal\",\"value\":[5]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(8, 'issue', 'My Open Issues', '{\"issue_owner\":{\"condition\":\"equal\",\"value\":[0]},\"issue_status_id\":{\"condition\":\"not_equal\",\"value\":[4]}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(9, 'issue', 'My Overdue Issues', '{\"issue_owner\":{\"condition\":\"equal\",\"value\":[0]},\"issue_status_id\":{\"condition\":\"not_equal\",\"value\":[4]},\"due_date\":{\"condition\":\"last\",\"value\":90}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(10, 'issue', 'My Closed Issues', '{\"issue_owner\":{\"condition\":\"equal\",\"value\":[0]},\"issue_status_id\":{\"condition\":\"equal\",\"value\":[4]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(11, 'issue', 'All Issues', NULL, 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(12, 'issue', 'Open Issues', '{\"issue_status_id\":{\"condition\":\"not_equal\",\"value\":[4]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(13, 'issue', 'Overdue Issues', '{\"issue_status_id\":{\"condition\":\"not_equal\",\"value\":[4]},\"due_date\":{\"condition\":\"last\",\"value\":90}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(14, 'issue', 'Closed Issues', '{\"issue_status_id\":{\"condition\":\"equal\",\"value\":[4]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(15, 'event', 'My Events', '{\"event_owner\":{\"condition\":\"equal\",\"value\":[0]}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(16, 'event', 'All Events', NULL, 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(17, 'project', 'My Active Projects', '{\"member\":{\"condition\":\"equal\",\"value\":[0]},\"project_status_id\":{\"condition\":\"not_equal\",\"value\":[7]}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(18, 'project', 'All Projects', NULL, 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(19, 'project', 'Active Projects', '{\"project_status_id\":{\"condition\":\"not_equal\",\"value\":[7]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(20, 'project', 'Archived Projects', '{\"project_status_id\":{\"condition\":\"equal\",\"value\":[7]}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(21, 'dashboard', 'All Data', '{\"timeperiod\":{\"condition\":null,\"value\":\"last_90_days\"},\"owner\":{\"condition\":\"all\",\"value\":null},\"widget_prefix\":{\"condition\":null,\"value\":null},\"auto_refresh\":{\"condition\":null,\"value\":15}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(22, 'dashboard', 'My Data', '{\"timeperiod\":{\"condition\":null,\"value\":\"last_90_days\"},\"owner\":{\"condition\":\"equal\",\"value\":[0]},\"widget_prefix\":{\"condition\":null,\"value\":\"My\"},\"auto_refresh\":{\"condition\":null,\"value\":15}}', 'everyone', NULL, 1, 0, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(23, 'notification', 'All My Notifications', '{\"timeperiod\":{\"condition\":null,\"value\":\"any\"},\"owner\":{\"condition\":\"all\",\"value\":null},\"related\":{\"condition\":null,\"value\":null}}', 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(24, 'staff', 'All Users', NULL, 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL),
(25, 'role', 'All Roles', NULL, 'everyone', NULL, 1, 1, '2021-01-22 02:11:22', '2021-01-22 02:11:22', NULL);

CREATE TABLE `followers` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linked_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `imports` (
  `id` int(10) UNSIGNED NOT NULL,
  `file_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `module_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `is_imported` tinyint(1) NOT NULL DEFAULT '0',
  `import_type` enum('new','update','update_overwrite') COLLATE utf8_unicode_ci NOT NULL,
  `created_data` longtext COLLATE utf8_unicode_ci,
  `updated_data` longtext COLLATE utf8_unicode_ci,
  `skipped_data` longtext COLLATE utf8_unicode_ci,
  `initial_data` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `issues` (
  `id` int(10) UNSIGNED NOT NULL,
  `issue_owner` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `issue_status_id` int(10) UNSIGNED NOT NULL,
  `issue_type_id` int(10) UNSIGNED DEFAULT NULL,
  `severity` enum('blocker','critical','major','minor','trivial') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reproducible` enum('always','sometimes','rarely','only_once','unable') COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linked_id` int(10) UNSIGNED DEFAULT NULL,
  `release_milestone_id` int(10) UNSIGNED DEFAULT NULL,
  `affected_milestone_id` int(10) UNSIGNED DEFAULT NULL,
  `access` enum('private','public','public_rwd') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `position` double UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `issue_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` double UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `category` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `issue_status` (`id`, `position`, `name`, `category`, `description`, `fixed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Open', 'open', NULL, 1, '2021-01-22 02:11:19', '2021-01-22 02:11:19', NULL),
(2, 2, 'In Progress', 'open', NULL, 0, '2021-01-22 02:11:19', '2021-01-22 02:11:19', NULL),
(3, 3, 'In Review', 'open', NULL, 0, '2021-01-22 02:11:19', '2021-01-22 02:11:19', NULL),
(4, 4, 'Closed', 'closed', NULL, 1, '2021-01-22 02:11:19', '2021-01-22 02:11:19', NULL);

CREATE TABLE `issue_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` double UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `issue_types` (`id`, `position`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Bug', NULL, '2021-01-22 02:11:20', '2021-01-22 02:11:20', NULL),
(2, 2, 'New Feature', NULL, '2021-01-22 02:11:20', '2021-01-22 02:11:20', NULL),
(3, 3, 'Enhancement', NULL, '2021-01-22 02:11:20', '2021-01-22 02:11:20', NULL),
(4, 4, 'Performance', NULL, '2021-01-22 02:11:20', '2021-01-22 02:11:20', NULL),
(5, 5, 'Security', NULL, '2021-01-22 02:11:20', '2021-01-22 02:11:20', NULL);

CREATE TABLE `milestones` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `milestone_owner` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `access` enum('private','public','public_rwd') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `position` double UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `note_info_id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `pin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `note_infos` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `notifications` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifiable_id` int(10) UNSIGNED NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('open','preserve','semi_preserve') COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` enum('general','project') COLLATE utf8_unicode_ci NOT NULL,
  `group` enum('basic','tool','import_export','admin_level') COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `display_name`, `type`, `description`, `label`, `group`) VALUES
(1, 'module.dashboard', 'Dashboard', 'open', 'Dashboard Module', 'general', 'basic'),
(2, 'module.project', 'Project', 'open', 'Project Module', 'general', 'basic'),
(3, 'module.task', 'Task', 'open', 'Task Module', 'general', 'basic'),
(4, 'module.issue', 'Issue', 'open', 'Issue Module', 'general', 'basic'),
(5, 'module.milestone', 'Milestone', 'open', 'Milestone Module', 'general', 'basic'),
(6, 'module.event', 'Event', 'open', 'Event Module', 'general', 'basic'),
(7, 'module.note', 'Note', 'open', 'Note', 'general', 'basic'),
(8, 'module.attachment', 'Attachment', 'open', 'Attachment', 'general', 'basic'),
(9, 'dashboard.view', 'View', 'open', 'View Dashboard', 'general', 'basic'),
(10, 'project.view', 'View', 'open', 'View Project List', 'general', 'basic'),
(11, 'project.create', 'Create', 'open', 'Create New Project', 'general', 'basic'),
(12, 'project.edit', 'Edit', 'open', 'Edit Project', 'general', 'basic'),
(13, 'project.delete', 'Delete', 'open', 'Delete Project', 'general', 'basic'),
(14, 'task.view', 'View', 'open', 'View Task List', 'general', 'basic'),
(15, 'task.create', 'Create', 'open', 'Create New Task', 'general', 'basic'),
(16, 'task.edit', 'Edit', 'open', 'Edit Task', 'general', 'basic'),
(17, 'task.delete', 'Delete', 'open', 'Delete Task', 'general', 'basic'),
(18, 'issue.view', 'View', 'open', 'View Issue List', 'general', 'basic'),
(19, 'issue.create', 'Create', 'open', 'Create New Issue', 'general', 'basic'),
(20, 'issue.edit', 'Edit', 'open', 'Edit Issue', 'general', 'basic'),
(21, 'issue.delete', 'Delete', 'open', 'Delete Issue', 'general', 'basic'),
(22, 'milestone.view', 'View', 'open', 'View Milestone List', 'general', 'basic'),
(23, 'milestone.create', 'Create', 'open', 'Create New Milestone', 'general', 'basic'),
(24, 'milestone.edit', 'Edit', 'open', 'Edit Milestone', 'general', 'basic'),
(25, 'milestone.delete', 'Delete', 'open', 'Delete Milestone', 'general', 'basic'),
(26, 'event.view', 'View', 'open', 'View Event List', 'general', 'basic'),
(27, 'event.create', 'Create', 'open', 'Create New Event', 'general', 'basic'),
(28, 'event.edit', 'Edit', 'open', 'Edit Event', 'general', 'basic'),
(29, 'event.delete', 'Delete', 'open', 'Delete Event', 'general', 'basic'),
(30, 'note.view', 'View', 'open', 'View Note', 'general', 'basic'),
(31, 'note.create', 'Create', 'open', 'Create New Note', 'general', 'basic'),
(32, 'note.edit', 'Edit', 'open', 'Edit Note', 'general', 'basic'),
(33, 'note.delete', 'Delete', 'open', 'Delete Note', 'general', 'basic'),
(34, 'attachment.view', 'View', 'open', 'View File', 'general', 'basic'),
(35, 'attachment.create', 'Create', 'open', 'Create New File', 'general', 'basic'),
(36, 'attachment.delete', 'Delete', 'open', 'Delete File', 'general', 'basic'),
(37, 'module.mass_update', 'Mass update', 'open', 'Mass update tool module', 'general', 'tool'),
(38, 'module.mass_delete', 'Mass delete', 'open', 'Mass delete tool module', 'general', 'tool'),
(39, 'module.change_owner', 'Change owner', 'open', 'Change owner tool module', 'general', 'tool'),
(40, 'mass_update.project', 'Project', 'open', 'Mass update projects', 'general', 'tool'),
(41, 'mass_update.task', 'Task', 'open', 'Mass update tasks', 'general', 'tool'),
(42, 'mass_update.issue', 'Issue', 'open', 'Mass update issues', 'general', 'tool'),
(43, 'mass_update.event', 'Event', 'open', 'Mass update events', 'general', 'tool'),
(44, 'mass_delete.project', 'Project', 'open', 'Mass delete projects', 'general', 'tool'),
(45, 'mass_delete.task', 'Task', 'open', 'Mass delete tasks', 'general', 'tool'),
(46, 'mass_delete.issue', 'Issue', 'open', 'Mass delete issues', 'general', 'tool'),
(47, 'mass_delete.event', 'Event', 'open', 'Mass delete events', 'general', 'tool'),
(48, 'mass_delete.user', 'User', 'open', 'Mass delete users', 'general', 'tool'),
(49, 'mass_delete.role', 'Role', 'open', 'Mass delete roles', 'general', 'tool'),
(50, 'change_owner.project', 'Project', 'open', 'Change project owner', 'general', 'tool'),
(51, 'change_owner.task', 'Task', 'open', 'Change task owner', 'general', 'tool'),
(52, 'change_owner.issue', 'Issue', 'open', 'Change issue owner', 'general', 'tool'),
(53, 'change_owner.milestone', 'Milestone', 'open', 'Change milestone owner', 'general', 'tool'),
(54, 'change_owner.event', 'Event', 'open', 'Change event owner', 'general', 'tool'),
(55, 'module.import', 'Import', 'open', 'Import module', 'general', 'import_export'),
(56, 'module.export', 'Export', 'open', 'Export module', 'general', 'import_export'),
(57, 'import.project', 'Project', 'open', 'Import projects', 'general', 'import_export'),
(58, 'import.task', 'Task', 'open', 'Import tasks', 'general', 'import_export'),
(59, 'import.issue', 'Issue', 'open', 'Import issues', 'general', 'import_export'),
(60, 'import.event', 'Event', 'open', 'Import events', 'general', 'import_export'),
(61, 'export.project', 'Project', 'open', 'Export projects', 'general', 'import_export'),
(62, 'export.task', 'Task', 'open', 'Export tasks', 'general', 'import_export'),
(63, 'export.issue', 'Issue', 'open', 'Export issues', 'general', 'import_export'),
(64, 'export.event', 'Event', 'open', 'Export events', 'general', 'import_export'),
(65, 'module.settings', 'Settings', 'open', 'Settings Module', 'general', 'admin_level'),
(66, 'module.custom_dropdowns', 'Custom dropdowns', 'open', 'Dropdown Module', 'general', 'admin_level'),
(67, 'module.user', 'User', 'open', 'User Module', 'general', 'admin_level'),
(68, 'module.role', 'Role', 'open', 'Role Module', 'general', 'admin_level'),
(69, 'settings.general', 'General', 'open', 'General Setting', 'general', 'admin_level'),
(70, 'settings.email', 'Email', 'open', 'Email Setting', 'general', 'admin_level'),
(71, 'custom_dropdowns.project_status.view', 'View', 'open', 'View Project Status List', 'general', 'admin_level'),
(72, 'custom_dropdowns.project_status.create', 'Create', 'open', 'Create New Project Status', 'general', 'admin_level'),
(73, 'custom_dropdowns.project_status.edit', 'Edit', 'open', 'Edit Project Status', 'general', 'admin_level'),
(74, 'custom_dropdowns.project_status.delete', 'Delete', 'open', 'Delete Project Status', 'general', 'admin_level'),
(75, 'custom_dropdowns.task_status.view', 'View', 'open', 'View Task Status List', 'general', 'admin_level'),
(76, 'custom_dropdowns.task_status.create', 'Create', 'open', 'Create New Task Status', 'general', 'admin_level'),
(77, 'custom_dropdowns.task_status.edit', 'Edit', 'open', 'Edit Task Status', 'general', 'admin_level'),
(78, 'custom_dropdowns.task_status.delete', 'Delete', 'open', 'Delete Task Status', 'general', 'admin_level'),
(79, 'custom_dropdowns.issue_status.view', 'View', 'open', 'View Issue Status List', 'general', 'admin_level'),
(80, 'custom_dropdowns.issue_status.create', 'Create', 'open', 'Create New Issue Status', 'general', 'admin_level'),
(81, 'custom_dropdowns.issue_status.edit', 'Edit', 'open', 'Edit Issue Status', 'general', 'admin_level'),
(82, 'custom_dropdowns.issue_status.delete', 'Delete', 'open', 'Delete Issue Status', 'general', 'admin_level'),
(83, 'custom_dropdowns.issue_type.view', 'View', 'open', 'View Issue Type List', 'general', 'admin_level'),
(84, 'custom_dropdowns.issue_type.create', 'Create', 'open', 'Create New Issue Type', 'general', 'admin_level'),
(85, 'custom_dropdowns.issue_type.edit', 'Edit', 'open', 'Edit Issue Type', 'general', 'admin_level'),
(86, 'custom_dropdowns.issue_type.delete', 'Delete', 'open', 'Delete Issue Type', 'general', 'admin_level'),
(87, 'user.view', 'View', 'open', 'View User List', 'general', 'admin_level'),
(88, 'user.create', 'Create', 'preserve', 'Create New User', 'general', 'admin_level'),
(89, 'user.edit', 'Edit', 'semi_preserve', 'Edit User except login credentials & role', 'general', 'admin_level'),
(90, 'user.delete', 'Delete', 'preserve', 'Delete User', 'general', 'admin_level'),
(91, 'role.view', 'View', 'open', 'View Role List', 'general', 'admin_level'),
(92, 'role.create', 'Create', 'preserve', 'Create New Role', 'general', 'admin_level'),
(93, 'role.edit', 'Edit', 'preserve', 'Edit Role', 'general', 'admin_level'),
(94, 'role.delete', 'Delete', 'preserve', 'Delete Role', 'general', 'admin_level');

CREATE TABLE `permission_role` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `permission_role` (`permission_id`, `role_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(1, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(2, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(2, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(3, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(3, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(4, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(4, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(5, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(5, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(6, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(6, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(7, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(7, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(8, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(8, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(9, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(9, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(10, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(10, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(11, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(11, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(12, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(12, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(13, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(13, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(14, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(14, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(15, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(15, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(16, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(16, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(17, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(17, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(18, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(18, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(19, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(19, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(20, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(20, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(21, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(21, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(22, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(22, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(23, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(23, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(24, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(24, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(25, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(25, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(26, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(26, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(27, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(27, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(28, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(28, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(29, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(29, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(30, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(30, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(31, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(31, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(32, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(32, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(33, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(33, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(34, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(34, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(35, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(35, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(36, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(36, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(37, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(37, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(38, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(38, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(39, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(39, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(40, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(40, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(41, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(41, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(42, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(42, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(43, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(43, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(44, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(44, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(45, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(45, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(46, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(46, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(47, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(47, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(48, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(48, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(49, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(49, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(50, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(50, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(51, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(51, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(52, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(52, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(53, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(53, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(54, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(54, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(55, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(55, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(56, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(56, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(57, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(57, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(58, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(58, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(59, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(59, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(60, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(60, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(61, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(61, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(62, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(62, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(63, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(63, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(64, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(64, 2, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(65, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(66, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(67, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(68, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(69, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(70, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(71, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(72, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(73, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(74, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(75, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(76, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(77, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(78, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(79, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(80, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(81, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(82, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(83, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(84, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(85, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(86, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(87, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(88, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(89, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(90, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(91, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(92, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(93, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL),
(94, 1, '2021-01-22 02:11:17', '2021-01-22 02:11:17', NULL);

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_owner` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `project_status_id` int(10) UNSIGNED NOT NULL,
  `access` enum('private','public','public_rwd') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `position` double UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `project_member` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `project_view` tinyint(1) NOT NULL DEFAULT '1',
  `project_edit` tinyint(1) NOT NULL DEFAULT '0',
  `project_delete` tinyint(1) NOT NULL DEFAULT '0',
  `member_view` tinyint(1) NOT NULL DEFAULT '0',
  `member_create` tinyint(1) NOT NULL DEFAULT '0',
  `member_edit` tinyint(1) NOT NULL DEFAULT '0',
  `member_delete` tinyint(1) NOT NULL DEFAULT '0',
  `milestone_view` tinyint(1) NOT NULL DEFAULT '1',
  `milestone_create` tinyint(1) NOT NULL DEFAULT '0',
  `milestone_edit` tinyint(1) NOT NULL DEFAULT '0',
  `milestone_delete` tinyint(1) NOT NULL DEFAULT '0',
  `task_view` tinyint(1) NOT NULL DEFAULT '1',
  `task_create` tinyint(1) NOT NULL DEFAULT '0',
  `task_edit` tinyint(1) NOT NULL DEFAULT '0',
  `task_delete` tinyint(1) NOT NULL DEFAULT '0',
  `issue_view` tinyint(1) NOT NULL DEFAULT '1',
  `issue_create` tinyint(1) NOT NULL DEFAULT '0',
  `issue_edit` tinyint(1) NOT NULL DEFAULT '0',
  `issue_delete` tinyint(1) NOT NULL DEFAULT '0',
  `event_view` tinyint(1) NOT NULL DEFAULT '1',
  `event_create` tinyint(1) NOT NULL DEFAULT '0',
  `event_edit` tinyint(1) NOT NULL DEFAULT '0',
  `event_delete` tinyint(1) NOT NULL DEFAULT '0',
  `note_view` tinyint(1) NOT NULL DEFAULT '0',
  `note_create` tinyint(1) NOT NULL DEFAULT '0',
  `note_edit` tinyint(1) NOT NULL DEFAULT '0',
  `note_delete` tinyint(1) NOT NULL DEFAULT '0',
  `attachment_view` tinyint(1) NOT NULL DEFAULT '0',
  `attachment_create` tinyint(1) NOT NULL DEFAULT '0',
  `attachment_delete` tinyint(1) NOT NULL DEFAULT '0',
  `gantt` tinyint(1) NOT NULL DEFAULT '0',
  `report` tinyint(1) NOT NULL DEFAULT '0',
  `history` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `project_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` double UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `category` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `project_status` (`id`, `position`, `name`, `category`, `description`, `fixed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Active', 'open', NULL, 1, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(2, 2, 'In Progress', 'open', NULL, 0, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(3, 3, 'On Track', 'open', NULL, 0, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(4, 4, 'Delayed', 'open', NULL, 0, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(5, 5, 'On Hold', 'open', NULL, 0, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(6, 6, 'Approved', 'open', NULL, 0, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL),
(7, 7, 'Completed', 'closed', NULL, 1, '2021-01-22 02:11:21', '2021-01-22 02:11:21', NULL);

CREATE TABLE `revisions` (
  `id` int(10) UNSIGNED NOT NULL,
  `revisionable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `revisionable_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8_unicode_ci,
  `new_value` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `label` enum('general','project') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `fixed`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'administrator', 'Administrator', 'This role will have all the permissions.', 1, 'general', '2021-01-22 02:11:16', '2021-01-22 02:11:16', NULL),
(2, 'standard', 'Standard', 'This role will have all the permissions except administrative privileges.', 1, 'general', '2021-01-22 02:11:16', '2021-01-22 02:11:16', NULL);

CREATE TABLE `role_user` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `settings` (`id`, `key`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'App name', 'NextPM', '2021-01-22 02:11:21', '2021-01-22 02:11:21'),
(2, 'logo', 'Logo', 'img/default-logo.png', '2021-01-22 02:11:21', '2021-01-22 02:11:21'),
(3, 'dark_logo', 'Dark Logo', 'img/default-dark-logo.png', '2021-01-22 02:11:21', '2021-01-22 02:11:21'),
(4, 'favicon', 'Favicon', 'img/default-favicon.png', '2021-01-22 02:11:22', '2021-01-22 02:11:22');

CREATE TABLE `social_media` (
  `id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` enum('staff') COLLATE utf8_unicode_ci NOT NULL,
  `media` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `staffs` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `signature` text COLLATE utf8_unicode_ci,
  `settings` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `staff_view` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `filter_view_id` int(10) UNSIGNED NOT NULL,
  `temp_params` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `task_owner` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `task_status_id` int(10) UNSIGNED NOT NULL,
  `completion_percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `priority` enum('high','highest','low','lowest','normal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `linked_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linked_id` int(10) UNSIGNED DEFAULT NULL,
  `milestone_id` int(10) UNSIGNED DEFAULT NULL,
  `access` enum('private','public','public_rwd') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `position` double UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `task_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` double UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `category` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL,
  `completion_percentage` tinyint(3) UNSIGNED NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `fixed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `task_status` (`id`, `position`, `name`, `category`, `completion_percentage`, `description`, `fixed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Not Started', 'open', 0, NULL, 1, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(2, 2, 'Deferred', 'open', 0, NULL, 0, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(3, 3, 'In Progress', 'open', 10, NULL, 0, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(4, 4, 'Waiting', 'open', 70, NULL, 0, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL),
(5, 5, 'Completed', 'closed', 100, NULL, 1, '2021-01-22 02:11:18', '2021-01-22 02:11:18', NULL);

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `linked_id` int(10) UNSIGNED NOT NULL,
  `linked_type` enum('staff') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'staff',
  `email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `allowed_staffs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `attach_files`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat_receivers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat_room_members`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat_senders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `event_attendees`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `filter_views`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `imports`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `issue_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `issue_status_name_unique` (`name`);

ALTER TABLE `issue_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `issue_types_name_unique` (`name`);

ALTER TABLE `milestones`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `note_infos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_id_notifiable_type_index` (`notifiable_id`,`notifiable_type`);

ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `project_member`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `project_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_status_name_unique` (`name`);

ALTER TABLE `revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `revisions_revisionable_id_revisionable_type_index` (`revisionable_id`,`revisionable_type`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

ALTER TABLE `social_media`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `staff_view`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `task_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_status_name_unique` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

ALTER TABLE `allowed_staffs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `attach_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chat_receivers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chat_room_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `chat_senders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_attendees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `filter_views`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

ALTER TABLE `followers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `imports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `issues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `issue_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `issue_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `milestones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `note_infos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `project_member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `project_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `revisions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `social_media`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `staffs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `staff_view`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `task_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
