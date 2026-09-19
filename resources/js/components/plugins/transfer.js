// Aides d'affichage pour les transferts de compte à compte et les taux de conversion.

const fmtAmount = (v) =>
    Number(v ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// Taux sans zéros inutiles : 2.350000 -> "2,35"
export function formatRate(rate) {
    if (rate === null || rate === undefined || rate === '') return '';
    return Number(rate).toLocaleString('fr-FR', { maximumFractionDigits: 6 });
}

// Symbole du sens du taux
export function directionSymbol(direction) {
    return direction === 'divide' ? '÷' : direction === 'multiply' ? '×' : '';
}

// Taux + sens : "× 2,35" ou "÷ 2,35" (chaîne vide si pas de taux)
export function rateWithDirection(mvt) {
    if (!mvt || mvt.rate === null || mvt.rate === undefined || mvt.rate === '') return '';
    const sym = directionSymbol(mvt.rate_direction);
    return sym ? `${sym} ${formatRate(mvt.rate)}` : formatRate(mvt.rate);
}

export const isTransfer = (mvt) => !!mvt?.transfer_ref;

function clientName(account) {
    const c = account?.client;
    return c ? `${c.nom ?? ''} ${c.prenom ?? ''}`.trim() : '';
}

/**
 * Libellé d'un mouvement de transfert, ex :
 *   "Transfert vers GMB-2026… — SOHE Gérard"
 *   "Transfert depuis GMB-2026… — SOHE Gérard"
 * Retourne '' si le mouvement n'est pas un transfert.
 */
export function transferLabel(mvt) {
    if (!isTransfer(mvt)) return '';
    const other = mvt.counterpart_account;
    const code = other?.code ?? '';
    const name = clientName(other);
    const target = [code, name].filter(Boolean).join(' — ');
    return `${mvt.type === 'withdraw' ? 'Transfert vers' : 'Transfert depuis'} ${target}`.trim();
}

/**
 * Détail de la conversion d'un transfert entre deux devises, ex :
 *   "1 000 000,00 XOF × 2,35 = 2 350 000,00 NRN"
 * Retourne '' si aucune conversion (même devise) ou si ce n'est pas un transfert.
 *
 * @param mvt                 le mouvement
 * @param accountCurrencyCode devise du compte auquel appartient le mouvement (si connue)
 */
export function transferConversion(mvt, accountCurrencyCode = '') {
    if (!isTransfer(mvt) || !mvt.rate || !mvt.rate_direction) return '';

    const amount = Number(mvt.amount);
    const rate = Number(mvt.rate);
    const sourceCode = mvt.currency?.code ?? '';   // devise du montant saisi = devise du compte source
    let received;
    let targetCode;

    if (mvt.type === 'deposit') {
        // Compte destination : final_amount = montant crédité
        received = Number(mvt.final_amount);
        targetCode = accountCurrencyCode || mvt.account?.currency?.code || '';
    } else {
        // Compte source : on recalcule ce que la destination a reçu
        received = mvt.rate_direction === 'divide' ? amount / rate : amount * rate;
        received = Math.round(received * 100) / 100;
        targetCode = mvt.counterpart_account?.currency?.code ?? '';
    }

    return `${fmtAmount(amount)} ${sourceCode} ${directionSymbol(mvt.rate_direction)} ${formatRate(rate)} = ${fmtAmount(received)} ${targetCode}`.trim();
}
