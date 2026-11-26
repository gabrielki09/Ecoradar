export interface WasteMaterial {
    name: string;
    description: string;
    icon: string;
    color: string;
    background: string;
    disposalGuide: string;
    source: { name: string; url: string };
}

export interface HowItWorksStep {
    number: string;
    title: string;
    description: string;
    icon: string;
}

