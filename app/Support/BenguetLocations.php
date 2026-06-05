<?php

namespace App\Support;

class BenguetLocations
{
    public const MUNICIPALITIES = [
        'ATOK',
        'BAKUN',
        'BOKOD',
        'BUGUIAS',
        'ITOGON',
        'KABAYAN',
        'KAPANGAN',
        'KIBUNGAN',
        'LA TRINIDAD',
        'MANKAYAN',
        'SABLAN',
        'TUBA',
        'TUBLAY',
    ];

    public const BARANGAYS_BY_MUNICIPALITY = [
        'ATOK' => ['ABIANG', 'CALIKING', 'CATTUBO', 'NAGUEY', 'PAOAY', 'PASDONG', 'POBLACION', 'TOPDAC'],
        'BAKUN' => ['AMPUSONGAN', 'BAGU', 'DALIPEY', 'GAMBANG', 'KAYAPA', 'POBLACION', 'SINACBAT'],
        'BOKOD' => ['AMBUKLAO', 'BILA', 'BOBOK-BISAL', 'DAKLAN', 'EKIP', 'KARAO', 'NAWAL', 'PITO', 'POBLACION', 'TIKEY'],
        'BUGUIAS' => ['ABATAN', 'AMGALEYGUEY', 'AMLIMAY', 'BACULONGAN NORTE', 'BACULONGAN SUR', 'BANGAO', 'BUYACAOAN', 'CALAMAGAN', 'CATLUBONG', 'LENGAOAN', 'LOO', 'NATUBLENG', 'POBLACION', 'SEBANG'],
        'ITOGON' => ['AMPUCAO', 'DALUPIRIP', 'GUMATDANG', 'LOACAN', 'POBLACION', 'TINONGDAN', 'TUDING', 'UCAB', 'VIRAC'],
        'KABAYAN' => ['ADAOAY', 'ANCHUKEY', 'BALLAY', 'BASHOY', 'BATAN', 'DUACAN', 'EDDET', 'GUSARAN', 'KABAYAN BARRIO', 'LUSOD', 'PACSO', 'POBLACION', 'TAWANGAN'],
        'KAPANGAN' => ['BALAKBAK', 'BELENG-BELIS', 'BOKLAOAN', 'CAYAPES', 'CUBA', 'DATAKAN', 'GADANG', 'GASWELING', 'LABUEG', 'PAYKEK', 'POBLACION CENTRAL', 'PONGAYAN', 'PUDONG', 'SAGUBO', 'TABA-AO'],
        'KIBUNGAN' => ['BADEO', 'LUBO', 'MADAYMEN', 'PALINA', 'POBLACION', 'SAGPAT', 'TACADANG'],
        'LA TRINIDAD' => ['ALAPANG', 'ALNO', 'AMBIONG', 'BAHONG', 'BALILI', 'BECKEL', 'BETAG', 'BINENG', 'CRUZ', 'LUBAS', 'PICO', 'POBLACION', 'PUGUIS', 'SHILAN', 'TAWANG', 'WANGAL'],
        'MANKAYAN' => ['BALILI', 'BEDBED', 'BULALACAO', 'CABITEN', 'COLALO', 'GUINAOANG', 'PACO', 'PALASAAN', 'POBLACION', 'SAPID', 'TABIO', 'TANEG'],
        'SABLAN' => ['BAGONG', 'BALLUAY', 'BANANGAN', 'BANENGBENG', 'BAYABAS', 'KAMOG', 'PAPPA', 'POBLACION'],
        'TUBA' => ['ANSAGAN', 'CAMP 3', 'CAMP 4', 'CAMP ONE', 'NANGALISAN', 'POBLACION', 'SAN PASCUAL', 'TABAAN NORTE', 'TABAAN SUR', 'TADIANGAN', 'TALOY NORTE', 'TALOY SUR', 'TWIN PEAKS'],
        'TUBLAY' => ['AMBASSADOR', 'AMBONGDOLAN', 'BA-AYAN', 'BASIL', 'CAPONGA (POB.)', 'DACLAN', 'TUBLAY CENTRAL', 'TUEL'],
    ];

    public static function normalize(?string $name): ?string
    {
        $normalized = strtoupper(trim((string) $name));
        $normalized = str_replace("\xc2\xa0", ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?: '';

        if ($normalized === '') {
            return null;
        }

        $compact = str_replace([' ', '_', '-'], '', $normalized);

        foreach (self::MUNICIPALITIES as $municipality) {
            if ($compact === str_replace(' ', '', $municipality)) {
                return $municipality;
            }
        }

        return $normalized;
    }

    public static function barangaysFor(?string $municipality): array
    {
        $municipality = self::normalize($municipality);

        return $municipality ? (self::BARANGAYS_BY_MUNICIPALITY[$municipality] ?? []) : [];
    }

    public static function isBarangayInMunicipality(?string $barangay, ?string $municipality): bool
    {
        $barangay = self::normalize($barangay);

        return $barangay !== null && in_array($barangay, self::barangaysFor($municipality), true);
    }
}
