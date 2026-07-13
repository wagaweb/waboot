import { format, parse } from 'date-fns';

export function getDayFromDate(date: Date|undefined): string {
    if(!date){
        return '';
    }
    return format(date,'dd/MM/yyyy');
}

export function getBackendFormatFromDate(date: Date|undefined): string {
    if(!date){
        return ''
    }
    return format(date,'yyyy-MM-dd');
}

export function getDateFromBackendFormat(backendFormattedDate: string): Date {
    return parse(backendFormattedDate,'yyyy-MM-dd', new Date());
}