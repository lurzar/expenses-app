import { PropsWithChildren } from 'react';

export default function PageContainer({ children, className = '' }: PropsWithChildren<{ className?: string }>) {
    return <div className={`app-page-container ${className}`}>{children}</div>;
}
