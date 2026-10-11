# the watr style

How my sites look, written down so every new one starts from the same place.

This folder is self-contained. Copy it into any project.

| file | what it is |
| --- | --- |
| `watr.css` | the kit. tokens, base styles and components, all inside `@layer watr` |
| `index.html` | live guide. open it in a browser, drag the knobs, copy the snippet |
| `assets/` | the blobs and the watrlabs icon |

Version 0.1. Taken from watrfiles (`public/assets/base.css`, the login, 404 and 500 pages).

---

## the idea

Every site gets the **keep** list unchanged. That's what makes it recognisably mine.
Every site changes the **knobs**. That's what stops them all looking the same.

If two of my sites sat side by side, you should be able to tell they're by the same person, and also tell them apart in under a second.

---

## keep (the signature)

1. **One hue, four depths.** Every accent comes from a single hue at four lightness steps. Nothing else gets a color, apart from red for danger and yellow for warning.

   | token | oklch | use |
   | --- | --- | --- |
   | `--watr-bright` | 78% · 0.13 · hue | buttons, highlights, selection |
   | `--watr-mid` | 69% · 0.11 · hue | button hover |
   | `--watr-bar` | 64% · 0.10 · hue | nav bar, footer, table headers |
   | `--watr-deep` | 41% · 0.07 · hue | disabled |

   At hue 196 these land on the original watrfiles colors (`#1dd0d1`, `#21afb0`, `#289e9f`, `#0b5657`).

2. **Light from the top-left.** Shadows are always offset down and to the right, with x, y and blur equal: `5px 5px 5px rgb(15 15 15 / .4)`. Never a centered glow, never a shadow going up.

3. **Framed by bars.** A full-width bar on top (45px), optional notice strips stacked directly under it (red, yellow, or bright), a bar at the bottom (50px). Each one casts its shadow onto whatever is below it.

4. **Square buttons, soft fields.** Buttons have 0 radius, bright fill, black text, 10px padding, darker on hover. Inputs are filled grey, no border, 0.3em radius. Keep that contrast.

5. **The thick rule.** Two-sided layouts put words on the left and the thing (a form, a file, a picture) on the right, split by a 5px ink line. On phones the rule turns horizontal.

6. **The blob.** Error and empty states show a big expressive blob emoji beside the message, on the right. On phones it moves above the text.

7. **Montserrat, lowercase chrome.** Montserrat for everything. Nav links and small labels are lowercase. Headings and sentences use normal case.

8. **Everything moves at 0.2s.** One speed for hover, open, close, and theme changes. Turned off for reduced motion.

9. **The voice.** See [voice](#voice).

---

## knobs (change per site)

Set these in the site's own CSS, after `watr.css`:

```css
:root {
    --watr-hue: 196;          /* 0-359. the one color */
    --watr-chroma: 1;         /* 0.4 calm, 1 normal, 1.3 loud */
    --watr-lift: 5px;         /* 3px flat, 5px normal, 8px dramatic */
    --watr-head-weight: 700;  /* 500 calm, 700 normal, 900 shouty */
}
```

The neutrals (page, surface, fields, text) all take a tiny bit of the hue too, so changing the hue changes the whole feel of the page, not only the buttons.

Other things that change per site but aren't CSS:

- **layout**: bar + sidebar for an app, split for auth and landing pages, one column for docs and text
- **the blob**: pick a face that fits the site. thinking for 404, panic for 500 is the watrfiles set
- **the quote**: one in-joke for the split panel, attributed like `- sword 2026`. never reuse one across sites
- **strips**: what the warning strip says, or whether the site has one at all

Rules for keeping sites different:

- Hues of live sites sit at least 40° apart. watrfiles is 196, the watrlabs logo is 237.
- Each new site changes at least one of intensity, lift or heading weight from the last one.
- Want one section of a page in a different color? Add `class="watr-scope"` and set `--watr-hue` on it.

---

## parts

All classes start with `watr-`. Everything is inside `@layer watr`, so any normal CSS you write overrides it without `!important`.

| part | classes | notes |
| --- | --- | --- |
| nav bar | `.watr-bar` `.watr-bar__group` `.watr-bar__home` | two groups, left and right. links lowercase except the site name |
| notice strip | `.watr-strip` `--warning` `--info` | red by default. goes straight under the bar |
| footer | `.watr-footer` | sticks to the bottom on short pages |
| button | `.watr-btn` `--quiet` `--danger`, `:disabled` | works on `<a>` and `<button>` |
| fields | plain `input`, `select`, `textarea` | styled by default, no class needed |
| message | `.watr-msg` `--warning` `--info` | solid block, no icon, no border |
| panel | `.watr-panel` | a raised block. use it for one thing per area, not every box |
| split | `.watr-split` `.watr-split__aside` `.watr-split__main` | the thick rule layout |
| blob | `.watr-blob` `.watr-blob__face` | error and empty states |
| quote | `.watr-quote` + `<cite>` | cite gets the `- ` prefix automatically |
| sidebar | `.watr-sidebar` `.is-hidden` | toggle `.is-hidden` with JS |
| modal | `.watr-modal` | dimmed full-screen backdrop |
| helpers | `.watr-row` `.watr-stack` `.watr-center` `img.watr-emoji` | |

Dark mode follows the system setting. `data-theme="dark"` or `data-theme="light"` on `<html>` forces one.

---

## voice

- Chrome (nav, labels, small buttons) is lowercase. Sentences are normal sentences.
- Say what happened and what to do: "Your passwords do not match." not "Oops! Something went wrong."
- Buttons say the action: Upload, Go back, Delete file. Not Submit or OK.
- One in-joke per page, max. It goes somewhere low-stakes, like the side of the login form, never in an error message someone needs to act on.
- Code comments can be as unhinged as you like.

---

## starting a new site

1. Link `watr.css` before your own CSS.
2. Pick a hue at least 40° from your other live sites. Tune it in `index.html` and copy the snippet.
3. Change at least one of intensity, lift or heading weight from the last site.
4. Pick the layout.
5. Pick the blob for 404 and 500, and write the site's one quote.
6. Go down the keep list. Add back anything missing.

---

## to work on

Things I noticed while pulling this out of watrfiles. Decide on each and move it into the guide.

- [ ] **White on the bar color is only 3.2:1 contrast.** Fine for bold nav links, too low for small body text. Options: darken `--watr-bar` to around 55% lightness, or keep it and never put small text on bars. (Black on bright is 11:1, no problem there.)
- [ ] **Yellow warnings had no text color** in the original CSS. The kit uses black text on yellow. Confirm that's the look.
- [ ] **`.container` in `base.css` has a broken background** (`background-color: --container-color;` is missing `var()`), so it never actually got its grey. The kit's `.watr-panel` uses the grey. Decide if panels should be grey or white.
- [ ] **Avatar + status dot** from `base.css` isn't in the kit yet.
- [ ] **A display moment.** Montserrat at 900 weight could be a thing for big landing headings. Try it on one site first.
- [ ] **Icons.** watrfiles loads Font Awesome but doesn't use it yet. Pick one icon set, or decide on none.
- [ ] **Move watrfiles onto the kit** so the source site and the guide stop drifting apart.

## changelog

- **0.1** (2026-10): first pull from watrfiles. ramp, drop shadow, bars, buttons, fields, split, blob, voice.
