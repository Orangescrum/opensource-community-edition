// Shared sign-off compose/parse used by both CommonSettings and EmailTemplateEdit.
// Stores in the DB as a single HTML string but presents to admins as three
// labelled fields: Greeting, Team name (bold), Tagline (smaller text).

export function escapeHtml(s) {
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

export function composeSignoff({ greeting, team, tagline }) {
    const g = (greeting || "").trim();
    const t = (team || "").trim();
    const tl = (tagline || "").trim();
    if (!g && !t && !tl) return "";
    const parts = [];
    if (g) parts.push(escapeHtml(g) + ",");
    if (t) parts.push(`<strong>${escapeHtml(t)}</strong>`);
    if (tl) parts.push(`<span style="color:#6b7280; font-size:12px;">${escapeHtml(tl)}</span>`);
    return parts.join("<br>");
}

// Best-effort: parse a stored sign-off HTML back into its 3 parts.
// Pattern emitted by composeSignoff():
//   {greeting},<br><strong>{team}</strong>[<br><span style=...>{tagline}</span>]
export function parseSignoff(html) {
    const out = { greeting: "", team: "", tagline: "" };
    if (!html) return out;
    const decoded = String(html)
        .replace(/&amp;/g, "&")
        .replace(/&middot;/g, "·")
        .replace(/&quot;/g, '"')
        .replace(/&lt;/g, "<")
        .replace(/&gt;/g, ">");
    const brMatch = decoded.match(/^([\s\S]*?)<br\s*\/?>/i);
    if (brMatch) out.greeting = brMatch[1].replace(/,\s*$/, "").trim();
    const strongMatch = decoded.match(/<strong>([\s\S]*?)<\/strong>/i);
    if (strongMatch) out.team = strongMatch[1].replace(/<[^>]+>/g, "").trim();
    const spanMatch = decoded.match(/<span[^>]*>([\s\S]*?)<\/span>/i);
    if (spanMatch) out.tagline = spanMatch[1].replace(/<[^>]+>/g, "").trim();
    return out;
}
