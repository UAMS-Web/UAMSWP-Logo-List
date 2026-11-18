Written against the unreleased `main` branch, after v2.0.2.

- [The block says to choose at least one category](#the-block-says-to-choose-at-least-one-category)
- [The block says no published logos match](#the-block-says-no-published-logos-match)
- [The block shows in the editor but not on the published page](#the-block-shows-in-the-editor-but-not-on-the-published-page)
- [A featured list shows only three logos](#a-featured-list-shows-only-three-logos)
- [An organization's name shows where its logo should be](#an-organizations-name-shows-where-its-logo-should-be)
- [A logo has a white or colored box around it](#a-logo-has-a-white-or-colored-box-around-it)
- [The more button does not appear](#the-more-button-does-not-appear)
- [Logo List Items is missing from the dashboard](#logo-list-items-is-missing-from-the-dashboard)

## The block says to choose at least one category

The editor shows *Click the pencil icon in the block toolbar and choose at least one category.*

**Cause:** **Categories to Display** is empty. A block without a category renders nothing.

**Fix:** open the block's settings and choose one or more categories under **List Configuration**.

## The block says no published logos match

The editor shows *No published logos match the selected categories and labels.*

**Cause:** no published Logo List Item satisfies the block's filters. Common reasons: the items are drafts; they are not in the chosen category; **Display Logic for Categories** or **Display Logic for Organization Labels** is set to match all, and no item carries every selected term; or the selected labels exclude every item the categories chose.

**Fix:** publish the items, check their **Categories** and **Label for Organization**, or switch the logic to match any. See [Choosing which logos a block shows](Choosing-which-logos-a-block-shows.md).

## The block shows in the editor but not on the published page

**Cause:** the editor placeholder is shown only while the block is being edited. On the published page a block with no category, or with no matching published item, outputs nothing at all.

**Fix:** as for the two entries above.

## A featured list shows only three logos

**Cause:** **Should this list be featured more prominently than other lists on the page?** is on. A featured list shows at most three items, whatever **Maximum Number of Items** says.

**Fix:** turn featuring off to show more.

## An organization's name shows where its logo should be

**Cause:** the item has no **Logo of Organization**, or its image was deleted from the media library. The block then shows the organization name in place of the logo.

**Fix:** upload the logo again on the item. See [Adding an organization](Adding-an-organization.md).

## A logo has a white or colored box around it

**Cause:** the image is not a PNG with a transparent background. A JPEG saved under a `.png` name keeps its solid background.

**Fix:** upload a true transparent PNG, cropped to the edges of the logo, at least 884 by 538 pixels.

## The more button does not appear

**Cause:** **Include link to something?** is on but its message or its **Button URL** is empty. The section is left out unless both are set.

**Fix:** fill in every field under **Linking Block to More**. See [Block settings](Block-settings.md#linking-block-to-more).

## Logo List Items is missing from the dashboard

**Cause:** the signed-in user's role lacks the plugin's capabilities. Only Administrator and Editor get them automatically.

**Fix:** sign in with one of those roles, or grant the capabilities. See [Permissions](Permissions.md).
