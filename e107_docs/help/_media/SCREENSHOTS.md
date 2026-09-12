# Screenshots used by the help renderer

The renderer resolves any `SHOT> path/to/file.ext | caption` marker against
this folder. Drop the matching file at the indicated path and it appears in
the help page automatically. Recommended width >= 900 px; the renderer
applies `img-responsive` so wider images downscale gracefully.

## Current screenshots

| File | Used in |
|------|---------|
| welcome/message-welcome.png | English/Welcome_Message |
| welcome/message-welcome-edit.png | English/Welcome_Message |
| preferences/theme-manager.png | English/Preferences |
| preferences/flood-protection.png | English/Preferences |
| users/user-classe-extended-fields.png | English/Users |
| users/tree-user.png | English/Users |
| administrators/admin-users.png | English/Administrators |
| banners/banner-manager-listing.png | English/Banners |
| banners/banner-admin.png | English/Banners |
| cache/admin-cache.png | English/Cache |
| downloads/admin-download.png | English/Downloads |
| forums/forum-admin.png | English/Forums |
| front-page/admin-frontpage.png | English/Front_Page |
| maintainance/admin-maintenance.png | English/Maintainance |
| menus/menus-admin.png | English/Menus |
| news/admin-newspost.png | English/News |

## Path resolution

The marker

    SHOT> welcome/message-welcome-edit.png | Welcome Message editor

is resolved against `e107_docs/help/_media/`, so the file above must live at

    e107_docs/help/_media/welcome/message-welcome-edit.png

If a file is missing the page simply shows a broken-image icon; the rest of
the document keeps rendering normally.
