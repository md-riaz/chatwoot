# Localization Review Checklist

- [ ] No new hardcoded user-visible strings in `src/routes/app/**` and `src/lib/components/**`.
- [ ] Placeholders use `$_('...')` keys.
- [ ] Button labels use `$_('...')` keys.
- [ ] Status badges/tabs use `$_('...')` keys.
- [ ] Empty states and loading labels use `$_('...')` keys.
- [ ] New English keys are added to `src/lib/i18n/locales/en/index.json` under existing namespaces.
- [ ] Run `pnpm check` before opening a PR.
