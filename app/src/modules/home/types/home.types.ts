export interface WasteMaterial {
    name: string;
    description: string;
    icon: string;
    color: string;
    background: string;
}

export interface HowItWorksStep {
    number: string;
    title: string;
    description: string;
    icon: string;
}

export interface CollectionPoint {
    id: number;
    name: string;
    address: string;
    distance: string;
    schedule: string;
    materials: string[];
    icon: string;
}
