export function useMoneyConfig(overrides: Partial<MoneyConfig> = {}) {
    const baseConfig: MoneyConfig = {
        prefix: 'R$ ',
        suffix: '',
        thousands: '.',
        decimal: ',',
        precision: 2,
        disableNegative: false,
        disabled: false,
        min: null,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 0,
        shouldRound: true,
        focusOnRight: false,
    };

    return { ...baseConfig, ...overrides };
}

export interface MoneyConfig {
    prefix: string;
    suffix: string;
    thousands: string;
    decimal: string;
    precision: number;
    disableNegative: boolean;
    disabled: boolean;
    min: number | null;
    max: number | null;
    allowBlank: boolean;
    minimumNumberOfCharacters: number;
    shouldRound: boolean;
    focusOnRight: boolean;
}
