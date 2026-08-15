declare module '@/ziggy' {
    const Ziggy: {
        url: string;
        port: number | null;
        defaults: Record<string, unknown>;
        routes: Record<string, {
            uri: string;
            methods: string[];
            parameters?: string[];
            bindings?: Record<string, string>;
        }>;
    };
    export { Ziggy };
}
