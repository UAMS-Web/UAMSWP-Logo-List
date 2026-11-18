Written against the unreleased `main` branch, after v2.0.2.

The **UAMS Logo List** block (`acf/logo-list`, in the editor's **Text** category under the keywords `uams`, `logos`, `organizations`, `images` and `graphics`) shows a section of organization logos. Its settings are an ACF field group, *Block: UAMS Logo List*, in three accordions. The block also supports an HTML anchor and the editor's **Additional CSS class(es)** setting.

The section renders only when at least one category is chosen and at least one published item matches. Otherwise the published page shows nothing, and the editor shows a placeholder saying what is missing.

## Block Configuration

| Setting | Type | Default | Effect |
| --- | --- | --- | --- |
| Heading | text, required | none | The section's `h2`. |
| Hide Heading | true/false | off | Hides the heading from sighted readers but keeps it for screen readers and search engines. Not recommended: the visible heading helps readers scan the page. |
| Description | textarea, required | none | A paragraph under the heading. The field asks for 179 characters at most but does not enforce it. |
| Background Color | select: White, Gray, Auto | Auto | The section's background class (`bg-white`, `bg-gray`, `bg-auto`). |

## List Configuration

| Setting | Type | Default | Effect |
| --- | --- | --- | --- |
| Categories to Display | Logo Item Categories, required | none | The categories whose items the block shows. |
| Display Logic for Categories | select | match any | **Display items that match any selected categories** (`IN`) or **Display only the items that match all selected categories** (`AND`). |
| Organizations Labels | Logo Item Labels | none | Narrows the items the categories chose to those that also carry these labels. |
| Display Logic for Organization Labels | select | match any | Shown once a label is selected. Any (`IN`) or all (`AND`) of the selected labels. |
| Should this list be featured more prominently than other lists on the page? | true/false | off | Larger logos and at most three items. Not for the only logo list on a page. |
| List Layout | select: Grid, Vertical List | Grid | A grid of logos, or a list with each logo beside its name, long description and link button. |
| Grid Width | select: Standard, Wide, Full | Full | Grid only. The list's width class (`aligncenter`, `alignwide`, `alignfull`). |
| Always Display Organization Titles? | true/false | off | Grid only. Shows each organization's name under its logo; otherwise the name is kept for screen readers only. The vertical list always shows names. |
| Display Organization Labels? | true/false | off | Shows each item's label. |
| Display Short Descriptions? | true/false | off | Grid only. Shows each item's short description. |
| Enable Links to Organizations? | true/false | off | Links each item that has a URL: its name in the grid, a button in the vertical list. |
| Open Links in New Window? | true/false | on | Shown once links are enabled. An item's own **Link Target** can override it. |
| Sort Order of List | select | Alphabetical by Post Title | Alphabetical by Organization Title, Alphabetical by Post Title, Menu Order (Low to High), Menu Order (High to Low) or Random Order. With one order other than random, ties are broken by organization name, or by menu order when the order is the organization name. |
| Maximum Number of Items | number | empty | The most items to show. Empty shows every match; a featured list shows three at most. |

## Linking Block to More

Shown when **Include link to something?** is on. The section appears only when its message and its link are both set.

| Setting | Type | Default | Effect |
| --- | --- | --- | --- |
| Include link to something? | true/false | off | Adds a message and a button below the list. |
| Message about availability of more information | text, required | none | The message above the button. |
| Button Text | text, required | none | The button's label. |
| Button URL | link, required | none | Where the button goes. A link set to open in a new tab adds `target="_blank"` and `rel="noopener"`. |
| Button Link Description | text, required | none | The button's accessible description for screen readers. |

## Example

A partner grid for a page about an example program: **Heading** `Our partners`, **Description** `Organizations that work with this program.`, **Categories to Display** `Example Partners`, **List Layout** Grid, **Always Display Organization Titles?** on, **Sort Order of List** Alphabetical by Organization Title.
