Written against the unreleased `main` branch, after v2.0.2.

The plugin gives Logo List Items and their two taxonomies their own capabilities, so access can be granted separately from posts and pages. On every request it adds all of them to the **Administrator** and **Editor** roles. Other roles get none of them unless a role editor grants them.

## Capabilities

| Capability | Allows |
| --- | --- |
| `edit_logo_list_item`, `read_logo_list_item`, `delete_logo_list_item` | Editing, reading and deleting one item. |
| `edit_logo_list_items` | Creating and editing items, and assigning categories and labels to them. |
| `edit_others_logo_list_items` | Editing items another user wrote. |
| `edit_published_logo_list_items`, `edit_private_logo_list_items` | Editing published or private items. |
| `publish_logo_list_items` | Publishing items. |
| `read_private_logo_list_items` | Reading private items. |
| `delete_logo_list_items`, `delete_others_logo_list_items`, `delete_published_logo_list_items`, `delete_private_logo_list_items` | Deleting items: one's own, others', published and private. |
| `manage_logo_list_taxonomies` | Creating, editing and deleting categories and labels. |

## Granting access to another role

Grant the capabilities above with a role editor. A role that should only add organizations needs `edit_logo_list_items` (and `publish_logo_list_items` to publish them), plus WordPress's own `upload_files` to upload logos; managing categories and labels also needs `manage_logo_list_taxonomies`.

**Note:** removing a capability from Administrator or Editor does not last, because the plugin adds the full set back to both roles on the next request.
