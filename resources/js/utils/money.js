export function formatMoney(amount) {
    return '$' + Number(amount).toFixed(2);
}

export function formatDelta(current, previous) {
    const delta = Number(current) - Number(previous);
    if (previous === 0 && current === 0) {
        return { delta: 0, label: 'Aucune donnée le mois précédent', tone: 'neutral' };
    }
    if (previous === 0) {
        return { delta, label: 'Nouveau ce mois-ci', tone: delta >= 0 ? 'positive' : 'negative' };
    }
    const pct = Math.round((delta / previous) * 100);
    const sign = delta > 0 ? '+' : '';
    return {
        delta,
        label: `${sign}${pct}% vs mois précédent`,
        tone: delta > 0 ? 'positive' : delta < 0 ? 'negative' : 'neutral',
    };
}
