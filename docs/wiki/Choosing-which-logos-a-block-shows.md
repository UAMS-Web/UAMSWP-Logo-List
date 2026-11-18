Written against the unreleased `main` branch, after v2.0.2.

A block shows the published Logo List Items that match its categories and, optionally, its labels, in the order and number its settings give. This guide covers each of those settings.

## Steps

1. **Choose the categories.** In **Categories to Display**, select one or more categories. **Display Logic for Categories** decides how several combine:
   - **Display items that match any selected categories** (the default) shows an item in at least one of them.
   - **Display only the items that match all selected categories** shows only items in every one of them.
2. **Narrow by label, if needed.** Select one or more **Organizations Labels**. The block then shows only the items, among those the categories chose, that also carry the labels. **Display Logic for Organization Labels** appears once a label is selected and works the same way as the category logic.
3. **Choose the order.** **Sort Order of List** offers alphabetical by organization name, alphabetical by post title, menu order (low to high or high to low) and random, and takes more than one: each order after the first breaks ties left by the ones before it. With exactly one order (other than random), the block adds its own tie-break: organization name, or menu order when the chosen order is the organization name. Menu order is the **Order** field under the item's attributes.
4. **Limit the number, if needed.** Enter **Maximum Number of Items**. Left empty, the block shows every matching item.
5. **Feature the list, if it is one of several on the page.** Turn on **Should this list be featured more prominently than other lists on the page?** A featured list shows larger logos and at most three items, whatever the maximum says. Do not feature the only logo list on a page.

**Note:** a list that matches nothing shows nothing on the published page. In the editor the block says *No published logos match the selected categories and labels.*

## Reference

- [Block settings](Block-settings.md): every setting named above, with its default.
- [Logo item fields](Logo-item-fields.md): the categories, label and organization name the settings read.
