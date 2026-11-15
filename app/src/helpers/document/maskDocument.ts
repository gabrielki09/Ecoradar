export default function cpfCnpjMask (val: string) {
    const cnpjCpf = val.replace(/\D/g, '');

    return cnpjCpf.length === 11 ? cnpjCpf.replace(
        /(\d{3})(\d{3})(\d{3})(\d{2})/g,
        '$1.$2.$3-$4'
    ) :  cnpjCpf.replace(
        /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,
        '$1.$2.$3/$4-$5'
    );
};
