export function isDebug(): boolean {
    //return import.meta.env.DEV;
    return true;
}

export function debugLog(prefix: string, debugThis: any|null = null){
    if(!isDebug()){
        return;
    }
    console.log(prefix);
    if(debugThis){
        console.log(debugThis);
    }
}

export function publicLog(prefix: string, debugThis: any|null = null){
    console.log(prefix);
    if(debugThis){
        console.log(debugThis);
    }
}