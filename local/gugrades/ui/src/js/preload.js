/**
 * Composable to preload aggregation data
 * (where possible)
 */

export function usePreload() {

    const recalculate = () => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        // Get all the level 1 categories.
        fetchMany([{
            methodname: 'local_gugrades_get_levelonecategories',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then(result => {
            let promises = []; 
            result.forEach(cat => {
                const catid = cat.id;
                const fullname = cat.fullname.toLowerCase();

                // Add only those that contain 'summative'
                // (better than nothing)
                if (fullname.includes('summative')) {
                    promises.push(

                        // Get the (detailed) tree for this top level category.
                        fetchMany([{
                            methodname: 'local_gugrades_recalculate',
                            args: {
                                courseid: courseid,
                                gradecategoryid: catid,
                            }
                        }])[0]
                        .then(result => {
                            console.log('Recalculated ' + cat.fullname)
                        })
                        .catch(error => {
                            console.error(error);
                        })
                    );
                }
            });

            Promise.all(promises).then(() => {
                console.log('Preload recalculate complete');
            })
        })
        .catch(error => {
            console.error(error);
        });
    }

    return { recalculate };
}