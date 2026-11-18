Written against the unreleased `main` branch, after v2.0.2.

By the end of this page you will have a page that shows a grid of organization logos, with a heading and a short description above it.

It assumes the plugin and the plugins it requires are installed and active (see [Installation (README)](https://github.com/UAMS-Web/UAMSWP-Logo-List#requirements-and-installation)), and that you are signed in as an administrator or an editor (see [Permissions](Permissions.md)).

## 1. Create a category

In the dashboard, open **Logo List Items**, then **Categories**. Add a category, for example `Example Partners`.

The category is what a block uses to choose its logos, so every list starts with one.

## 2. Add two or three organizations

Open **Logo List Items**, then **Add New**. For each organization:

1. Enter a post title. It is used only in the dashboard and for one sort order.
2. Fill in **Name of Organization**, for example `Example Health Partner`.
3. Upload a PNG logo with a transparent background under **Logo of Organization**.
4. Tick the `Example Partners` category under **Categories**.
5. Select **Publish**.

The organizations now appear in the **Logo List Items** list, with their category in its own column. [Adding an organization](Adding-an-organization.md) covers the other fields.

## 3. Add the block to a page

Edit a page, add a block and search for `logo`. Choose **UAMS Logo List**.

The block shows a dashed placeholder in the editor: *Click the pencil icon in the block toolbar and choose at least one category.*

## 4. Configure the block

Open the block's settings:

1. Under **Block Configuration**, enter a **Heading** and a **Description** (both are required).
2. Under **List Configuration**, choose `Example Partners` in **Categories to Display**.
3. Leave **List Layout** at **Grid** and **Sort Order of List** at **Alphabetical by Post Title**.

The placeholder is replaced by a preview of the logo grid.

**Note:** if the placeholder now says *No published logos match the selected categories and labels.*, none of the organizations in that category is published yet. See [Troubleshooting](Troubleshooting.md).

## 5. Publish the page

Update or publish the page and view it. The section shows the heading, the description and one logo per organization.

## Go further

- [Choosing which logos a block shows](Choosing-which-logos-a-block-shows.md): narrow a list by label, change the sort order, limit or feature it.
- [Block settings](Block-settings.md): every setting the block offers.
