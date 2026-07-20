<?php

// config/plans.php
// Source unique de vérité pour les tarifs des offres IADial.
// Utilisé par PaymentController (facturation réelle) et le dashboard admin
// (calcul du revenu récurrent mensuel basé sur la colonne users.plan).

return [
    'starter' => [
        'label' => 'IADial Starter',
        'amount_eur' => 399,
    ],
    'pro' => [
        'label' => 'IADial Pro',
        'amount_eur' => 599,
    ],
    // 'business' est sur devis : pas de prix fixe, donc absent ici
    // volontairement. Voir DevisController pour ce flux.
];