import { HTMLAttributes, PropsWithChildren } from 'react';

export default function Surface({ children, className = '', ...props }: PropsWithChildren<HTMLAttributes<HTMLDivElement>>) {
    return <div className={`ui-surface ${className}`} {...props}>{children}</div>;
}
