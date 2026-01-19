export const KEYS = {
  ALT: "Alt / ⌥",
  WIN: "Win / ⌘",
  SHIFT: "Shift",
  SLASH: "/",
  UP: "Up",
  DOWN: "Down",
} as const;

export const SHORTCUT_KEYS = [
  {
    id: 1,
    label: "OPEN_CONVERSATION",
    displayKeys: [KEYS.ALT, "J", KEYS.SLASH, KEYS.ALT, "K"],
    keySet: ["Alt+KeyJ", "Alt+KeyK"],
  },
  {
    id: 2,
    label: "RESOLVE_AND_NEXT",
    displayKeys: [KEYS.WIN, KEYS.ALT, "E"],
    keySet: ["$mod+Alt+KeyE"],
  },
  {
    id: 3,
    label: "NAVIGATE_DROPDOWN",
    displayKeys: [KEYS.UP, KEYS.DOWN],
    keySet: ["ArrowUp", "ArrowDown"],
  },
  {
    id: 4,
    label: "RESOLVE_CONVERSATION",
    displayKeys: [KEYS.ALT, "E"],
    keySet: ["Alt+KeyE"],
  },
  {
    id: 5,
    label: "GO_TO_CONVERSATION_DASHBOARD",
    displayKeys: [KEYS.ALT, "C"],
    keySet: ["Alt+KeyC"],
  },
  {
    id: 6,
    label: "ADD_ATTACHMENT",
    displayKeys: [KEYS.WIN, KEYS.ALT, "A"],
    keySet: ["$mod+Alt+KeyA"],
  },
  {
    id: 7,
    label: "GO_TO_CONTACTS_DASHBOARD",
    displayKeys: [KEYS.ALT, "V"],
    keySet: ["Alt+KeyV"],
  },
  {
    id: 8,
    label: "TOGGLE_SIDEBAR",
    displayKeys: [KEYS.ALT, "O"],
    keySet: ["Alt+KeyO"],
  },
  {
    id: 9,
    label: "GO_TO_REPORTS_SIDEBAR",
    displayKeys: [KEYS.ALT, "R"],
    keySet: ["Alt+KeyR"],
  },
  {
    id: 10,
    label: "MOVE_TO_NEXT_TAB",
    displayKeys: [KEYS.ALT, "N"],
    keySet: ["Alt+KeyN"],
  },
  {
    id: 11,
    label: "GO_TO_SETTINGS",
    displayKeys: [KEYS.ALT, "S"],
    keySet: ["Alt+KeyS"],
  },
  {
    id: 12,
    label: "SWITCH_TO_PRIVATE_NOTE",
    displayKeys: [KEYS.ALT, "P"],
    keySet: ["Alt+KeyP"],
  },
  {
    id: 13,
    label: "SWITCH_TO_REPLY",
    displayKeys: [KEYS.ALT, "L"],
    keySet: ["Alt+KeyL"],
  },
  {
    id: 14,
    label: "TOGGLE_SNOOZE_DROPDOWN",
    displayKeys: [KEYS.ALT, "M"],
    keySet: ["Alt+KeyM"],
  },
] as const;

export const SHORTCUT_TITLES: Record<string, string> = {
  OPEN_CONVERSATION: "Open conversation",
  RESOLVE_AND_NEXT: "Resolve and go to next",
  NAVIGATE_DROPDOWN: "Navigate dropdown items",
  RESOLVE_CONVERSATION: "Resolve conversation",
  GO_TO_CONVERSATION_DASHBOARD: "Go to conversation dashboard",
  ADD_ATTACHMENT: "Add attachment",
  GO_TO_CONTACTS_DASHBOARD: "Go to contacts dashboard",
  TOGGLE_SIDEBAR: "Toggle sidebar",
  GO_TO_REPORTS_SIDEBAR: "Go to reports sidebar",
  MOVE_TO_NEXT_TAB: "Move to next conversation tab",
  GO_TO_SETTINGS: "Go to settings",
  SWITCH_TO_PRIVATE_NOTE: "Switch to private note",
  SWITCH_TO_REPLY: "Switch to reply",
  TOGGLE_SNOOZE_DROPDOWN: "Toggle snooze dropdown",
};

export const LAYOUT_QWERTY = "QWERTY";
export const LAYOUT_QWERTZ = "QWERTZ";
export const LAYOUT_AZERTY = "AZERTY";

export const keysToModifyInQWERTZ = new Set(["Alt+KeyP", "Alt+KeyL"]);

async function detectLegacyLayout(): Promise<string> {
  if (typeof document === "undefined") {
    return LAYOUT_QWERTY;
  }

  const input = document.createElement("input");
  input.style.position = "fixed";
  input.style.top = "-100px";
  document.body.appendChild(input);
  input.focus();

  return new Promise((resolve) => {
    const keyboardEvent = new KeyboardEvent("keypress", {
      key: "y",
      keyCode: 89,
      which: 89,
      bubbles: true,
      cancelable: true,
    });

    const handler = (e: KeyboardEvent) => {
      document.body.removeChild(input);
      document.removeEventListener("keypress", handler);

      if (e.key === "z") {
        resolve(LAYOUT_QWERTY);
      } else if (e.key === "y") {
        resolve(LAYOUT_QWERTZ);
      } else {
        resolve(LAYOUT_AZERTY);
      }
    };

    document.addEventListener("keypress", handler);
    input.dispatchEvent(keyboardEvent);
  });
}

async function detectLayout(): Promise<string> {
  const nav: any = typeof navigator !== "undefined" ? navigator : null;
  if (!nav?.keyboard || typeof nav.keyboard.getLayoutMap !== "function") {
    return detectLegacyLayout();
  }

  const map = await nav.keyboard.getLayoutMap();
  const q = map.get("KeyQ");
  const w = map.get("KeyW");
  const e = map.get("KeyE");
  const r = map.get("KeyR");
  const t = map.get("KeyT");
  const y = map.get("KeyY");

  return [q, w, e, r, t, y].join("").toUpperCase();
}

export async function detectKeyboardLayout(): Promise<string> {
  if (typeof window === "undefined") {
    return LAYOUT_QWERTY;
  }

  const cachedLayout = (window as any).cw_keyboard_layout;
  if (cachedLayout) {
    return cachedLayout;
  }

  const layout = await detectLayout();
  (window as any).cw_keyboard_layout = layout;
  return layout;
}

export function needsShiftKeyForLayout(
  keySet: readonly string[],
  layout: string | null,
): boolean {
  return (
    layout === LAYOUT_QWERTZ &&
    keySet.some((key) => keysToModifyInQWERTZ.has(key))
  );
}

