Written against the unreleased `main` branch, after v2.0.2.

A **Logo List Item** (post type `logo-list-item`) is one organization a logo list can show. Its fields are the ACF field group *Logo List Items*, loaded from the plugin's `assets/json/acf-json/` folder. The post type supports a title, an author, revisions, custom fields and page attributes (for menu order), and is available in the REST API at `logo_list_item`.

## Fields

| Field | Name | Type | Required | Where it is shown |
| --- | --- | --- | --- | --- |
| Name of Organization | `logo_title` | text | yes | The item's heading in every layout, and the **Alphabetical by Organization Title** sort. Use the full name, not an acronym. |
| Logo of Organization | `logo_image` | image | no | The logo. A PNG with a transparent background, cropped to the logo, at least 884 by 538 pixels. Without one, or when the image was deleted from the media library, the organization name is shown instead. |
| URL for Organization | `logo_link` | URL | no | The link target when the block enables links. |
| Link Text | `logo_link_text` | text | no | Shown once a URL is set. The vertical list's button text; defaults to `Visit Their Website`. |
| Link Target | `logo_link_target` | radio | yes, once a URL is set | **No Override** (`inherit`) follows the block; **Always Open in New Window** (`_blank`) or **Always Open in Same Window** (`_self`) overrides it for this item. |
| Categories | `logo_category` | Logo Item Categories (multi-select) | no | Not shown. Chooses which blocks include the item. |
| Label for Organization | `logo_label` | Logo Item Labels | no | Shown when the block displays labels, and used to narrow a list. Examples: industry, city, state. |
| Short Description | `logo_shortdescr` | textarea | no | The grid layout, when the block displays short descriptions. 235 characters at most. |
| Long Description | `logo_longdescr` | WYSIWYG | no | The vertical list layout. |

The block never shows the post title, and it is used by the **Alphabetical by Post Title** sort. It is still public: each published item has its own page, the post type has an archive, and items appear in site search results, all under the post title. Keep internal notes out of it.

## Taxonomies

| Taxonomy | Slug | Structure | Menu |
| --- | --- | --- | --- |
| Logo Item Categories | `logo-list-category` | hierarchical | **Logo List Items**, then **Categories** |
| Logo Item Labels | `logo-list-label` | flat | **Logo List Items**, then **Labels** |

Neither taxonomy has public archive pages. Both show as columns in the **Logo List Items** list, and are chosen on the item through its **Categories** and **Label for Organization** fields rather than the usual side boxes.
