'use client';
import { useRouter } from "next/navigation";
import { deletePost } from "../../../helper/moderatorServerActions";

export default function DeleteBtn({id}){
    const router= useRouter();

    async function delPost(){
       const result = await deletePost(id);
       console.log(result);
       if(result){
        router.refresh();
       }
    }

    return(
        <form action={delPost}>
            <button type="submit" className="flex gap-2 bg-red-600 text-white px-3 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" strokeLinecap="round" strokeWidth="1.5" 
                d="M9.17 4a3.001 3.001 0 0 1 5.66 0m5.67 2h-17m15.333 2.5l-.46 6.9c-.177 2.654-.265 3.981-1.13 4.79c-.865.81-2.196.81-4.856.81h-.774c-2.66 
                0-3.991 0-4.856-.81c-.865-.809-.954-2.136-1.13-4.79l-.46-6.9M9.5 11l.5 5m4.5-5l-.5 5"/></svg>
                Delete
            </button>
        </form>
    );
}